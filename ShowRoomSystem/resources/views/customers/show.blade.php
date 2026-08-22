@extends('layouts.app')

@section('title', 'Customer Details')
@section('page', $customer->name)

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600;">{{ $customer->name }}</h2>
            <div>
                @if(session('user_role') === 'editor')
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline">Edit</a>
                @endif
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Back</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Customer ID</p>
                <p style="font-size:1.2rem; font-weight:600;">#{{ $customer->id }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Phone Number</p>
                <p style="font-size:1.2rem;">{{ $customer->phone_number ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Google Drive Folder</p>
                @if($customer->google_drive_link)
                    <a href="{{ $customer->google_drive_link }}" target="_blank" style="color: #0d6efd; text-decoration: none;">
                        Open Folder
                    </a>
                @else
                    <p style="color: #9ca3af;">—</p>
                @endif
            </div>
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Member Since</p>
                <p>{{ $customer->created_at->format('F d, Y') }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Last Updated</p>
                <p>{{ $customer->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <h3 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 1rem;">Cars</h3>

        @if($customer->cars->count())
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>Model Year</th>
                            <th>Remaining Balance</th>
                            <th>Remaining Months</th>
                            <th>Drive Folder</th>
                            <th>Purchase Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->cars as $car)
                            @php
                                $purchase = $car->purchase;
                                $startingBalance = max(0, ($purchase->overall_price ?? 0) - ($purchase->upfront_payment ?? 0));
                                $paidAmount = $car->rentPayments->sum('amount');
                                $paidMonths = $car->rentPayments->sum('months_count');
                                $remainingBalance = max(0, $startingBalance - $paidAmount);
                                $remainingMonths = $purchase && $purchase->months ? max(0, $purchase->months - $paidMonths) : 0;
                            @endphp
                            <tr>
                                <td>{{ $car->name }}</td>
                                <td>{{ $car->model_year }}</td>
                                <td>${{ number_format($remainingBalance, 2) }}</td>
                                <td>{{ $remainingMonths }}</td>
                                <td>
                                    @if($car->drive_link)
                                        <a href="{{ $car->drive_link }}" target="_blank" class="btn btn-outline" style="padding:0.3rem 0.8rem;">Open</a>
                                    @else
                                        <span style="color:#6b7280;">Not created</span>
                                    @endif
                                </td>
                                <td>
                                    @if($car->purchase_receipt_file_id)
                                        <a href="https://drive.google.com/file/d/{{ $car->purchase_receipt_file_id }}/view" target="_blank" class="btn btn-outline" style="padding:0.3rem 0.8rem;">Open</a>
                                    @else
                                        <span style="color:#6b7280;">Not uploaded</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="color:#6b7280;">No cars recorded for this customer yet.</p>
        @endif
    </div>
@endsection
