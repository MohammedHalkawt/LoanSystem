@extends('layouts.app')

@section('title', 'Customers')
@section('page', 'All Customers')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600;">Customers</h2>
            @if(session('user_role') === 'editor')
                <a href="{{ route('customers.create') }}" class="btn btn-primary">+ New Customer</a>
            @endif
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('customers.index') }}" class="search-box">
            <input type="text" name="search" class="form-control" placeholder="Search by name or phone..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Search</button>
            @if(request('search'))
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>

        <!-- Customers Table -->
        @if($customers->count())
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Folder Path</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                        <tr>
                            <td>#{{ $customer->id }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone_number ?? '—' }}</td>
                            <td style="font-size:0.8rem; color:#6b7280;">{{ $customer->folder_path ?? '—' }}</td>
                            <td>{{ $customer->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline" style="padding:0.3rem 0.8rem; margin-right:0.3rem;">View</a>
                                @if(session('user_role') === 'editor')
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline" style="padding:0.3rem 0.8rem;">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1.5rem;">
                {{ $customers->links() }}
            </div>
        @else
            <p style="text-align: center; color: #6b7280; padding: 2rem;">No customers found.</p>
        @endif
    </div>
@endsection