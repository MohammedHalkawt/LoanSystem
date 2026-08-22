@extends('layouts.app')

@section('title', 'Rent Details')
@section('page', 'Rent Details')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600;">Rent Payment Details</h2>
        <a href="{{ route('rents.index') }}" class="btn btn-outline">Back</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Customer</p>
            <p style="font-size:1.1rem; font-weight:600;">
                <a href="{{ route('customers.show', $rent->customer) }}" style="color: #0d6efd; text-decoration: none;">
                    {{ $rent->customer->name ?? 'N/A' }}
                </a>
            </p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Car</p>
            <p style="font-size:1.1rem; font-weight:600;">{{ $rent->car->name }} ({{ $rent->car->model_year }})</p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Amount Paid</p>
            <p style="font-size:1.1rem;">${{ number_format($rent->amount, 2) }}</p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Payment Date</p>
            <p style="font-size:1.1rem;">{{ $rent->payment_date->format('F d, Y') }}</p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Receipt</p>
            @if($rent->receipt_drive_link)
                <a href="{{ $rent->receipt_drive_link }}" target="_blank" class="btn btn-outline">Open Receipt</a>
            @else
                <p style="font-size:1.1rem; color:#6b7280;">Not uploaded</p>
            @endif
        </div>
        <div style="grid-column: 1 / -1;">
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Notes</p>
            <p style="font-size:1.1rem; white-space:pre-wrap;">{{ $rent->notes ?: 'No notes.' }}</p>
        </div>
    </div>
</div>
@endsection
