@extends('layouts.app')

@section('title', 'Rent Payments')
@section('page', 'Rent')

@section('content')
<div class="card" style="padding: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600;">Rent Payments</h2>

        @if(session('user_role') === 'editor')
            <a href="{{ route('rents.create') }}" class="btn btn-primary">+ Record Rent</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert" style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('rents.index') }}" class="search-box" style="margin-bottom: 2rem;">
        <input type="text" name="search" placeholder="Search by customer or car..." value="{{ request('search') }}" class="form-control">
        <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.8rem 2rem;">Search</button>
        @if(request('search'))
            <a href="{{ route('rents.index') }}" class="btn btn-outline">Clear</a>
        @endif
    </form>

    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Amount</th>
                    <th>Covered Months</th>
                    <th>Payment Date</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rents as $rent)
                    <tr>
                        <td>{{ $rent->id }}</td>
                        <td>{{ $rent->customer->name ?? 'N/A' }}</td>
                        <td>{{ $rent->car->name ?? 'N/A' }} {{ $rent->car ? '(' . $rent->car->model_year . ')' : '' }}</td>
                        <td>${{ number_format($rent->amount, 2) }}</td>
                        <td>{{ $rent->covered_month_from }} to {{ $rent->covered_month_to }}</td>
                        <td>{{ $rent->payment_date->format('Y-m-d') }}</td>
                        <td>
                            @if($rent->receipt_drive_link)
                                <a href="{{ $rent->receipt_drive_link }}" target="_blank" class="btn btn-outline" style="padding:0.3rem 0.8rem;">Open</a>
                            @else
                                <span style="color:#6b7280;">Not uploaded</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('rents.show', $rent) }}" class="btn btn-outline" style="padding:0.3rem 0.8rem;">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #6b7280;">No rent payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 2rem;">
        {{ $rents->links() }}
    </div>
</div>
@endsection
