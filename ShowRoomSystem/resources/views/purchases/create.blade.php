@extends('layouts.app')

@section('content')
<div class="card" style="max-width: 700px; margin: 0 auto;">
    <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 2rem;">Record New Purchase</h2>

    <form method="POST" action="{{ route('purchases.store') }}">
        @csrf

        {{-- Customer dropdown --}}
        <div class="form-group">
            <label for="customer_id">Customer <span style="color: #ef4444;">*</span></label>
            <select name="customer_id" id="customer_id" class="form-control" required>
                <option value="">Select a customer</option>
                <option value="new" style="color: #3b82f6; font-weight: 600;">+ New Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Car details --}}
        <div class="form-group">
            <label for="car_name">Car Name <span style="color: #ef4444;">*</span></label>
            <input type="text" name="car_name" id="car_name" class="form-control" value="{{ old('car_name') }}" required>
            @error('car_name')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="model_year">Model Year <span style="color: #ef4444;">*</span></label>
            <input type="number" name="model_year" id="model_year" class="form-control" value="{{ old('model_year') }}" min="1900" max="{{ date('Y')+1 }}" required>
            @error('model_year')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        {{-- Pricing --}}
        <div class="form-group">
            <label for="overall_price">Overall Price ($) <span style="color: #ef4444;">*</span></label>
            <input type="number" step="0.01" name="overall_price" id="overall_price" class="form-control" value="{{ old('overall_price') }}" required>
            @error('overall_price')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="basic_price">Basic Price ($) <span style="color: #ef4444;">*</span></label>
            <input type="number" step="0.01" name="basic_price" id="basic_price" class="form-control" value="{{ old('basic_price') }}" required>
            @error('basic_price')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="upfront_payment">Upfront Payment ($) <span style="color: #ef4444;">*</span></label>
            <input type="number" step="0.01" name="upfront_payment" id="upfront_payment" class="form-control" value="{{ old('upfront_payment') }}" required>
            @error('upfront_payment')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="months">Months</label>
            <input type="number" name="months" id="months" class="form-control" value="{{ old('months') }}">
        </div>

        {{-- Purchase date --}}
        <div class="form-group">
            <label for="purchase_date">Purchase Date</label>
            <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}">
            @error('purchase_date')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Optional notes for this purchase...">{{ old('notes') }}</textarea>
            @error('notes')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        {{-- Form actions --}}
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Record Purchase</button>
            <a href="{{ route('purchases.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>

    <script>
        document.getElementById('customer_id').addEventListener('change', function () {
            if (this.value === 'new') {
                window.location.href = "{{ route('customers.create') }}?redirect=purchase";
            }
        });
    </script>
</div>
@endsection
