@extends('layouts.app')

@section('title', 'Purchase Details')
@section('page', 'Purchase #' . $purchase->id)

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600;">Purchase #{{ $purchase->id }}</h2>
        <div style="display: flex; gap: 0.5rem;">
            @if(session('user_role') === 'editor')
                <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-outline">Edit</a>
            @endif
            <a href="{{ route('purchases.index') }}" class="btn btn-outline">Back</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Customer</p>
            <p style="font-size:1.1rem; font-weight:600;">
                <a href="{{ route('customers.show', $purchase->customer) }}" style="color: #0d6efd; text-decoration: none;">
                    {{ $purchase->customer->name ?? 'N/A' }}
                </a>
            </p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Car</p>
            <p style="font-size:1.1rem; font-weight:600;">{{ $purchase->car_name }} ({{ $purchase->model_year }})</p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Overall Price</p>
            <p style="font-size:1.1rem;">${{ number_format($purchase->overall_price, 2) }}</p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Basic Price</p>
            <p style="font-size:1.1rem;">${{ number_format($purchase->basic_price, 2) }}</p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Upfront Payment</p>
            <p style="font-size:1.1rem;">${{ number_format($purchase->upfront_payment, 2) }}</p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Remaining Balance</p>
            <p style="font-size:1.1rem; font-weight:600; color: #ef4444;">${{ number_format($purchase->overall_price - $purchase->upfront_payment, 2) }}</p>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Months</p>
            @if(!$purchase->months || $purchase->months == 0)
                <p style="font-size:1.1rem; font-weight:600; color: #10b981;">✓ Completed</p>
            @else
                <p style="font-size:1.1rem;">{{ $purchase->months }} months</p>
            @endif
        </div>
        <div>
            <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Purchase Date</p>
            <p style="font-size:1.1rem;">{{ $purchase->purchase_date->format('F d, Y') }}</p>
        </div>
    </div>
</div>
@endsection