@extends('layouts.app')

@section('title', 'Edit Customer')
@section('page', 'Edit ' . $customer->name)

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Edit Customer</h2>

        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Name <span style="color: #e53e3e;">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                @error('name') <p style="color: #e53e3e; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number', $customer->phone_number) }}">
                @error('phone_number') <p style="color: #e53e3e; font-size:0.8rem;">{{ $message }}</p> @enderror
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Update Customer</button>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>

        @if(session('user_role') === 'editor')
            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 2rem 0 1rem;">
            <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="background: none; border: 1px solid #e53e3e; color: #e53e3e;">Delete Customer</button>
            </form>
        @endif
    </div>
@endsection
