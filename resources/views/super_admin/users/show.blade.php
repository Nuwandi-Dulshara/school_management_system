@extends('layouts.super_admin', ['pageTitle' => 'User Details'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>User Details</h1>
        <p>Account profile and access information.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('super_admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <a href="{{ route('super_admin.users.edit', $managedUser) }}" class="btn btn-academic">
            <i class="bi bi-pencil me-2"></i>Edit User
        </a>
    </div>
</div>

<div class="border rounded-3 p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <span class="user-avatar" style="width: 60px; height: 60px; font-size: 1.15rem;">
            {{ strtoupper(substr($managedUser->first_name, 0, 1) . substr($managedUser->last_name, 0, 1)) }}
        </span>
        <div>
            <h4 class="text-navy fw-bold mb-1">{{ $managedUser->first_name }} {{ $managedUser->last_name }}</h4>
            <span class="status-badge status-{{ $managedUser->status }}">{{ ucfirst($managedUser->status) }}</span>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6"><div class="detail-label">Name</div><p class="detail-value">{{ $managedUser->first_name }} {{ $managedUser->last_name }}</p></div>
        <div class="col-md-6"><div class="detail-label">Email</div><p class="detail-value">{{ $managedUser->email }}</p></div>
        <div class="col-md-6"><div class="detail-label">Username</div><p class="detail-value">{{ $managedUser->username }}</p></div>
        <div class="col-md-6"><div class="detail-label">Role</div><p class="detail-value">{{ $roles[$managedUser->role] ?? ucfirst($managedUser->role) }}</p></div>
        <div class="col-md-6"><div class="detail-label">Status</div><p class="detail-value">{{ ucfirst($managedUser->status) }}</p></div>
        <div class="col-md-6"><div class="detail-label">Created Date</div><p class="detail-value">{{ $managedUser->created_at->format('F j, Y') }}</p></div>
    </div>
</div>

<div class="border rounded-3 p-4">
    <h5 class="text-navy fw-bold mb-1">Reset Password</h5>
    <p class="text-muted mb-4">Set a new password for this user account.</p>
    <form method="POST" action="{{ route('super_admin.users.password', $managedUser) }}">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-5">
                <label for="password" class="form-label fw-semibold">New Password</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-academic w-100" type="submit">Reset</button>
            </div>
        </div>
    </form>
</div>
@endsection
