@extends('layouts.super_admin', ['pageTitle' => 'Edit Profile'])

@section('super_admin_content')
@include('profile._styles')
@php($picture = $user->profile_picture ? asset('storage/'.$user->profile_picture) : asset('images/default-profile.svg'))
<div class="profile-shell">
    <div class="page-heading"><div><h1>Edit Profile</h1><p>Update your personal account information and profile picture.</p></div></div>
    <div class="card profile-card"><div class="card-body p-4 p-lg-5">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="d-flex flex-column flex-sm-row align-items-center gap-3 mb-4 pb-4 border-bottom">
                <img src="{{ $picture }}" alt="Current profile picture" class="profile-upload-preview">
                <div class="flex-grow-1 w-100">
                    <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="form-control @error('profile_picture') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">JPG, PNG, or WebP. Maximum size 2 MB.</div>
                    @error('profile_picture')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12"><label for="full_name" class="form-label">Full Name</label><input id="full_name" name="full_name" value="{{ old('full_name', trim($user->first_name.' '.$user->last_name)) }}" class="form-control @error('full_name') is-invalid @enderror" required>@error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label for="username" class="form-label">Username</label><input id="username" name="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required>@error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label for="email" class="form-label">Email</label><input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label for="phone" class="form-label">Phone Number</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label for="address" class="form-label">Address</label><textarea id="address" name="address" rows="4" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
            <div class="alert alert-light border mt-4 mb-0"><i class="bi bi-shield-lock me-2 text-primary"></i>Your role and account status cannot be changed here.</div>
            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4"><a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>Save Changes</button></div>
        </form>
    </div></div>
</div>
@endsection
