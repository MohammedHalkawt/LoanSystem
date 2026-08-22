@extends('layouts.app')

@section('content')
<div class="card" style="padding: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600;">Purchase Records</h2>

        {{-- Add button only for editors --}}
        @if(session('user_role') === 'editor')
            <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ New Purchase</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert" style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;">{{ session('success') }}</div>
    @endif

    {{-- Search form --}}
    <form method="GET" action="{{ route('purchases.index') }}" class="search-box" style="margin-bottom: 2rem;">
        <input type="text" name="search" placeholder="Search by customer or car..." value="{{ request('search') }}" class="form-control">
        <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.8rem 2rem;">Search</button>
        @if(request('search'))
            <a href="{{ route('purchases.index') }}" class="btn btn-outline">Clear</a>
        @endif
    </form>

    {{-- Purchases table --}}
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Model Year</th>
                    <th>Overall Price</th>
                    <th>Upfront</th>
                    <th>Balance</th>
                    <th>Purchase Date</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->id }}</td>
                        <td>{{ $purchase->customer->name ?? 'N/A' }}</td>
                        <td>{{ $purchase->car_name }}</td>
                        <td>{{ $purchase->model_year }}</td>
                        <td>${{ number_format($purchase->overall_price, 2) }}</td>
                        <td>${{ number_format($purchase->upfront_payment, 2) }}</td>
                        <td>${{ number_format($purchase->overall_price - $purchase->upfront_payment, 2) }}</td>
                        <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                        <td>
                            @if($purchase->car?->purchase_receipt_file_id)
                                <a href="https://drive.google.com/file/d/{{ $purchase->car->purchase_receipt_file_id }}/view" target="_blank" class="btn btn-outline" style="padding:0.3rem 0.8rem;">Open</a>
                            @else
                                <span style="color:#6b7280;">Not uploaded</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline" style="padding:0.3rem 0.8rem; margin-right:0.3rem;">View</a>
                            @if(session('user_role') === 'editor')
                                <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-outline" style="padding:0.3rem 0.8rem; margin-right:0.3rem;">Edit</a>
                                <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this purchase?')" class="btn btn-outline" style="padding:0.3rem 0.8rem; color: #ef4444; border-color: #ef4444;">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2rem; color: #6b7280;">No purchases found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="margin-top: 2rem;">
        {{ $purchases->links() }}
    </div>
</div>
@endsection
