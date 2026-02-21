@extends('layouts.app')

@section('content')
    <div class="welcome-header">
        <h2>Welcome back, {{ session('user_name') }}!</h2>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Total Customers</div>
                <div class="stat-value">{{ $totalCustomers }}</div>
            </div>
            <div class="stat-icon">👥</div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Total Purchases</div>
                <div class="stat-value">{{ $totalPurchases }}</div>
            </div>
            <div class="stat-icon">🚗</div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">${{ number_format($totalRevenue, 0) }}</div>
            </div>
            <div class="stat-icon">💰</div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Rent Payments</div>
                <div class="stat-value">${{ number_format($totalRentPayments, 0) }}</div>
            </div>
            <div class="stat-icon">📅</div>
        </div>
    </div>

    @if(session('user_role') === 'editor')
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="actions-grid">
                <a href="{{ route('customers.create') }}" class="action-btn">
                    <div class="action-title">➕ Add Customer</div>
                    <div class="action-desc">Register a new customer</div>
                </a>
                <a href="{{ route('purchases.create') }}" class="action-btn">
                    <div class="action-title">📝 Record Purchase</div>
                    <div class="action-desc">Add a new car purchase</div>
                </a>

                {{-- Show rent button only if route exists (to avoid error) --}}
                @if(Route::has('rents.create'))
                    <a href="{{ route('rents.create') }}" class="action-btn">
                        <div class="action-title">💰 Record Rent</div>
                        <div class="action-desc">Log a rent payment</div>
                    </a>
                @else
                    {{-- Placeholder for future development --}}
                    <div class="action-btn" style="opacity: 0.6; cursor: not-allowed;" onclick="return false;">
                        <div class="action-title">💰 Record Rent</div>
                        <div class="action-desc">Coming soon</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

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