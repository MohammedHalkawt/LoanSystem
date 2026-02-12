@extends('layouts.app')

@section('title', 'Create Customer')
@section('page', 'New Customer')

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Add New Customer</h2>

        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Name <span style="color: #e53e3e;">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                @error('name') <p style="color: #e53e3e; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number') }}">
                @error('phone_number') <p style="color: #e53e3e; font-size:0.8rem;">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="folder_path">Folder Path (for files)</label>
                <input type="text" name="folder_path" id="folder_path" class="form-control" value="{{ old('folder_path') }}" placeholder="e.g. customers/acme">
                @error('folder_path') <p style="color: #e53e3e; font-size:0.8rem;">{{ $message }}</p> @enderror
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Save Customer</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection