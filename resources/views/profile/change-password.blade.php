@extends('layouts.super_admin', ['pageTitle' => 'Change Password'])

@section('super_admin_content')
@include('profile._styles')
<div class="profile-shell">
    <div class="page-heading"><div><h1>Change Password</h1><p>Confirm your current password before setting a new one.</p></div></div>
    <div class="card profile-card"><div class="card-body p-4 p-lg-5">
        <form method="POST" action="{{ route('profile.password.update') }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-12"><label for="current_password" class="form-label">Current Password</label><input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label for="password" class="form-label">New Password</label><input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label for="password_confirmation" class="form-label">Confirm New Password</label><input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required></div>
            </div>
            <div class="form-text mt-3">Use at least 8 characters and avoid reusing an old password.</div>
            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4"><a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-academic"><i class="bi bi-key me-2"></i>Update Password</button></div>
        </form>
    </div></div>
</div>
@endsection
