@extends('layouts.app')

@section('title', 'Customer Details')
@section('page', $customer->name)

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600;">{{ $customer->name }}</h2>
            <div>
                @if(session('user_role') === 'editor')
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline">Edit</a>
                @endif
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Back</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Customer ID</p>
                <p style="font-size:1.2rem; font-weight:600;">#{{ $customer->id }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Phone Number</p>
                <p style="font-size:1.2rem;">{{ $customer->phone_number ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Google Drive Folder</p>
                @if($customer->google_drive_link)
                    <a href="{{ $customer->google_drive_link }}" target="_blank" style="color: #0d6efd; text-decoration: none;">
                        Open Folder
                    </a>
                @else
                    <p style="color: #9ca3af;">—</p>
                @endif
            </div>
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Member Since</p>
                <p>{{ $customer->created_at->format('F d, Y') }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:0.2rem;">Last Updated</p>
                <p>{{ $customer->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
@endsection