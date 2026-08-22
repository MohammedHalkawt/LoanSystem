@extends('layouts.app')

@section('title', 'Record Rent')
@section('page', 'Record Rent')

@section('content')
<div class="card" style="max-width: 760px; margin: 0 auto;">
    <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 2rem;">Record Rent Payment</h2>

    @if($errors->any())
        <div class="alert" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;">
            Please check the form and try again.
        </div>
    @endif

    <form method="POST" action="{{ route('rents.store') }}">
        @csrf

        <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">

        <div class="form-group">
            <label for="customer_search">Customer <span style="color: #ef4444;">*</span></label>
            <input
                type="text"
                id="customer_search"
                class="form-control"
                list="customer_suggestions"
                autocomplete="off"
                placeholder="Start typing a customer name..."
                required
            >
            <datalist id="customer_suggestions">
                @foreach($customers as $customer)
                    <option value="#{{ $customer->id }} {{ $customer->name }} - {{ $customer->phone_number ?: 'No phone' }}"></option>
                @endforeach
            </datalist>
            @error('customer_id')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="car_id">Car <span style="color: #ef4444;">*</span></label>
            <select name="car_id" id="car_id" class="form-control" required disabled>
                <option value="">Select a customer first</option>
            </select>
            @error('car_id')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="padding:1rem; border-radius:16px; background:#f9fafb; border:1px solid #e5e7eb;">
                <div style="font-size:0.75rem; color:#6b7280; text-transform:uppercase;">Remaining Balance</div>
                <div id="remaining_balance" style="font-size:1.35rem; font-weight:700;">$0.00</div>
            </div>
            <div style="padding:1rem; border-radius:16px; background:#f9fafb; border:1px solid #e5e7eb;">
                <div style="font-size:0.75rem; color:#6b7280; text-transform:uppercase;">Per Month</div>
                <div id="monthly_amount" style="font-size:1.35rem; font-weight:700;">$0.00</div>
            </div>
            <div style="padding:1rem; border-radius:16px; background:#f9fafb; border:1px solid #e5e7eb;">
                <div style="font-size:0.75rem; color:#6b7280; text-transform:uppercase;">Remaining Months</div>
                <div id="remaining_months" style="font-size:1.35rem; font-weight:700;">0</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="amount">Amount Paid ($) <span style="color: #ef4444;">*</span></label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
                @error('amount')
                    <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="payment_date">Payment Date</label>
                <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}">
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="covered_month_from">Month From <span style="color: #ef4444;">*</span></label>
                <input type="month" name="covered_month_from" id="covered_month_from" class="form-control" value="{{ old('covered_month_from', date('Y-m')) }}" required>
                @error('covered_month_from')
                    <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="covered_month_to">Month To <span style="color: #ef4444;">*</span></label>
                <input type="month" name="covered_month_to" id="covered_month_to" class="form-control" value="{{ old('covered_month_to', date('Y-m')) }}" required>
                @error('covered_month_to')
                    <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Record Rent</button>
            <a href="{{ route('rents.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<script>
    const customers = @json($customers->map(fn ($customer) => [
        'id' => $customer->id,
        'label' => '#' . $customer->id . ' ' . $customer->name . ' - ' . ($customer->phone_number ?: 'No phone'),
    ])->values());
    const cars = @json($cars);
    const oldCustomerId = "{{ old('customer_id') }}";
    const oldCarId = "{{ old('car_id') }}";

    const customerSearch = document.getElementById('customer_search');
    const customerId = document.getElementById('customer_id');
    const carSelect = document.getElementById('car_id');
    const remainingBalance = document.getElementById('remaining_balance');
    const monthlyAmount = document.getElementById('monthly_amount');
    const remainingMonths = document.getElementById('remaining_months');

    function money(value) {
        return '$' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fillCars(selectedCustomerId) {
        const customerCars = cars.filter(car => String(car.customer_id) === String(selectedCustomerId));
        carSelect.innerHTML = '';

        if (!customerCars.length) {
            carSelect.disabled = true;
            carSelect.innerHTML = '<option value="">No cars found for this customer</option>';
            updateSummary(null);
            return;
        }

        carSelect.disabled = false;
        carSelect.innerHTML = '<option value="">Select a car</option>';

        customerCars.forEach(car => {
            const option = document.createElement('option');
            option.value = car.id;
            option.textContent = car.label;
            carSelect.appendChild(option);
        });

        if (oldCarId) {
            carSelect.value = oldCarId;
        }

        updateSummary(cars.find(car => String(car.id) === String(carSelect.value)));
    }

    function updateSummary(car) {
        remainingBalance.textContent = money(car?.remaining_balance);
        monthlyAmount.textContent = money(car?.monthly_amount);
        remainingMonths.textContent = car?.remaining_months ?? 0;
    }

    customerSearch.addEventListener('input', function () {
        const customer = customers.find(item => item.label === this.value);

        customerId.value = customer ? customer.id : '';
        fillCars(customer ? customer.id : null);
    });

    carSelect.addEventListener('change', function () {
        updateSummary(cars.find(car => String(car.id) === String(this.value)));
    });

    if (oldCustomerId) {
        const customer = customers.find(item => String(item.id) === String(oldCustomerId));
        if (customer) {
            customerSearch.value = customer.label;
            customerId.value = customer.id;
            fillCars(customer.id);
        }
    }
</script>
@endsection
