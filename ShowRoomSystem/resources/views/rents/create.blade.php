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

        <style>
            .autocomplete-wrap {
                position: relative;
            }

            .autocomplete-results {
                position: absolute;
                top: calc(100% + 0.35rem);
                left: 0;
                right: 0;
                z-index: 30;
                background: white;
                border: 1px solid #d1d5db;
                border-radius: 16px;
                box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
                max-height: 260px;
                overflow-y: auto;
                display: none;
            }

            .autocomplete-option {
                width: 100%;
                display: block;
                text-align: left;
                padding: 0.9rem 1rem;
                background: white;
                color: #111827;
                border: 0;
                border-bottom: 1px solid #f3f4f6;
                border-radius: 0;
                margin: 0;
                box-shadow: none;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
            }

            .autocomplete-option:hover {
                background: #f9fafb;
                transform: none;
                box-shadow: none;
            }

            .autocomplete-meta {
                display: block;
                margin-top: 0.15rem;
                font-size: 0.78rem;
                font-weight: 500;
                color: #6b7280;
            }
        </style>

        <div class="form-group autocomplete-wrap">
            <label for="customer_search">Customer <span style="color: #ef4444;">*</span></label>
            <input
                type="text"
                id="customer_search"
                class="form-control"
                autocomplete="off"
                placeholder="Start typing a customer name..."
                required
            >
            <div id="customer_results" class="autocomplete-results"></div>
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

        <div style="padding:1rem; border-radius:16px; background:#f9fafb; border:1px solid #e5e7eb; margin-bottom: 1.5rem;">
            <div style="font-size:0.75rem; color:#6b7280; text-transform:uppercase;">Calculated Coverage</div>
            <div id="coverage_preview" style="font-size:1rem; font-weight:600; margin-top:0.35rem;">Select a car and enter an amount.</div>
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

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Optional note for this rent payment...">{{ old('notes') }}</textarea>
            @error('notes')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Record Rent</button>
            <a href="{{ route('rents.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<script>
    const customers = @json($customerOptions);
    const cars = @json($cars);
    const oldCustomerId = "{{ old('customer_id') }}";
    const oldCarId = "{{ old('car_id') }}";

    const customerSearch = document.getElementById('customer_search');
    const customerResults = document.getElementById('customer_results');
    const customerId = document.getElementById('customer_id');
    const carSelect = document.getElementById('car_id');
    const amountInput = document.getElementById('amount');
    const remainingBalance = document.getElementById('remaining_balance');
    const monthlyAmount = document.getElementById('monthly_amount');
    const remainingMonths = document.getElementById('remaining_months');
    const coveragePreview = document.getElementById('coverage_preview');

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
            updateCoveragePreview();
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
        updateCoveragePreview();
    }

    function updateSummary(car) {
        remainingBalance.textContent = money(car?.remaining_balance);
        monthlyAmount.textContent = money(car?.monthly_amount);
        remainingMonths.textContent = car?.remaining_months ?? 0;
    }

    function selectedCar() {
        return cars.find(car => String(car.id) === String(carSelect.value));
    }

    function addMonths(date, months) {
        const next = new Date(date.getTime());
        next.setMonth(next.getMonth() + months);
        return next;
    }

    function formatMonth(date) {
        return date.toLocaleString(undefined, { month: 'long', year: 'numeric' });
    }

    function updateCoveragePreview() {
        const car = selectedCar();
        const amount = Number(amountInput.value || 0);

        if (!car || amount <= 0) {
            coveragePreview.textContent = 'Select a car and enter an amount.';
            return;
        }

        if (amount > Number(car.remaining_balance) + 0.009) {
            coveragePreview.textContent = 'This is more than the remaining balance and cannot be recorded.';
            return;
        }

        const balanceAfterPayment = Math.max(0, Number(car.remaining_balance) - amount);
        const nextMonthlyAmount = Number(car.remaining_months) > 0
            ? balanceAfterPayment / Number(car.remaining_months)
            : 0;

        if (balanceAfterPayment <= 0.009) {
            coveragePreview.textContent = 'This will fully pay the remaining balance.';
            return;
        }

        coveragePreview.textContent = 'Balance after payment: ' + money(balanceAfterPayment)
            + '. New per-month amount: ' + money(nextMonthlyAmount) + '.';
    }

    function showCustomerResults() {
        const search = customerSearch.value.trim().toLowerCase();
        const matches = customers
            .filter(customer => !search || customer.label.toLowerCase().includes(search))
            .slice(0, 8);

        customerResults.innerHTML = '';

        if (!matches.length) {
            customerResults.style.display = 'none';
            return;
        }

        matches.forEach(customer => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'autocomplete-option';
            button.innerHTML = customer.label + '<span class="autocomplete-meta">' + customer.phone + '</span>';
            button.addEventListener('click', function () {
                customerSearch.value = customer.label;
                customerId.value = customer.id;
                customerResults.style.display = 'none';
                fillCars(customer.id);
            });
            customerResults.appendChild(button);
        });

        customerResults.style.display = 'block';
    }

    customerSearch.addEventListener('input', function () {
        const exactMatches = customers.filter(item => item.label === this.value);
        const customer = exactMatches.length === 1 ? exactMatches[0] : null;

        customerId.value = customer ? customer.id : '';
        fillCars(customer ? customer.id : null);
        showCustomerResults();
    });

    customerSearch.addEventListener('focus', showCustomerResults);

    document.addEventListener('click', function (event) {
        if (!customerResults.contains(event.target) && event.target !== customerSearch) {
            customerResults.style.display = 'none';
        }
    });

    carSelect.addEventListener('change', function () {
        updateSummary(cars.find(car => String(car.id) === String(this.value)));
        updateCoveragePreview();
    });

    amountInput.addEventListener('input', updateCoveragePreview);

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
