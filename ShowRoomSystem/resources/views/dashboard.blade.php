@extends('layouts.app')

@section('content')
    <div class="welcome-header">
        <h2>{{ session('user_name') }}</h2>
    </div>

@if(session('user_role') === 'editor')
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
            <a href="{{ route('customers.create') }}" class="action-btn">
                <div class="action-title">Add Customer</div>
                <div class="action-desc">Register a new customer</div>
            </a>
            <a href="{{ route('purchases.create') }}" class="action-btn">
                <div class="action-title">Record Purchase</div>
                <div class="action-desc">Add a new car purchase</div>
            </a>
            <a href="{{ route('rents.create') }}" class="action-btn">
                <div class="action-title">Record Rent</div>
                <div class="action-desc">Log a rent payment</div>
            </a>
            <button type="button" class="action-btn" onclick="openReportModal()">
                <div class="action-title">Monthly Report</div>
                <div class="action-desc">Print a monthly summary</div>
            </button>
        </div>
    </div>
@elseif(session('user_role') === 'viewer')
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
            <a href="{{ route('customers.index') }}" class="action-btn">
                <div class="action-title">View Customers</div>
                <div class="action-desc">Browse all customers</div>
            </a>
            <a href="{{ route('purchases.index') }}" class="action-btn">
                <div class="action-title">View Purchases</div>
                <div class="action-desc">Browse all purchases</div>
            </a>
            <a href="{{ route('rents.index') }}" class="action-btn">
                <div class="action-title">View Rents</div>
                <div class="action-desc">Browse all rent payments</div>
            </a>
            <button type="button" class="action-btn" onclick="openReportModal()">
                <div class="action-title">Monthly Report</div>
                <div class="action-desc">Print a monthly summary</div>
            </button>
        </div>
    </div>
@endif

    <div id="reportModal" class="modal-backdrop" style="display:none;">
        <div class="modal-panel">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.25rem;">
                <div>
                    <h3 style="font-size:1.2rem; font-weight:700;">Monthly Report</h3>
                    <p style="color:#6b7280; font-size:0.9rem;">Choose one month or a range.</p>
                </div>
                <button type="button" class="icon-close" onclick="closeReportModal()">×</button>
            </div>

            <form method="GET" action="{{ route('reports.monthly') }}" target="_blank">
                <div class="form-group">
                    <label for="start_month">From Month</label>
                    <input type="month" name="start_month" id="start_month" class="form-control" value="{{ date('Y-m') }}" required>
                </div>
                <div class="form-group">
                    <label for="end_month">To Month</label>
                    <input type="month" name="end_month" id="end_month" class="form-control" value="{{ date('Y-m') }}">
                </div>
                <div style="display:flex; gap:0.75rem;">
                    <button type="submit" class="btn btn-primary">Print Report</button>
                    <button type="button" class="btn btn-outline" onclick="closeReportModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReportModal() {
            document.getElementById('reportModal').style.display = 'flex';
        }

        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
        }

        document.getElementById('reportModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeReportModal();
            }
        });
    </script>

    {{-- Recent purchases --}}
    <div class="card" style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1rem;">Recent Purchases</h3>
        @php
            $recentPurchases = \App\Models\Purchase::with('customer')->latest()->limit(5)->get();
        @endphp
        <table class="table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPurchases as $purchase)
                <tr>
                    <td>{{ $purchase->customer->name ?? 'N/A' }}</td>
                    <td>{{ $purchase->car_name }}</td>
                    <td>${{ number_format($purchase->overall_price, 2) }}</td>
                    <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #6b7280;">No purchases yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
