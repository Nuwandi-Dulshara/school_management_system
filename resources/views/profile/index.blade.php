@extends('layouts.super_admin', ['pageTitle' => 'My Profile'])

@section('super_admin_content')
@include('profile._styles')
@php
    $picture = $user->profile_picture ? asset('storage/'.$user->profile_picture) : asset('images/default-profile.svg');
    $roleLabel = ucwords(str_replace('_', ' ', $user->role));
@endphp
<div class="profile-shell">
    <div class="page-heading">
        <div><h1>My Profile</h1><p>Review your account details and role information.</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('profile.edit') }}" class="btn btn-academic"><i class="bi bi-pencil-square me-2"></i>Edit Profile</a>
            <a href="{{ route('profile.password.edit') }}" class="btn btn-outline-secondary"><i class="bi bi-key me-2"></i>Change Password</a>
        </div>
    </div>

    <div class="card profile-card mb-4">
        <div class="profile-hero d-flex flex-column flex-md-row align-items-center gap-4 text-center text-md-start">
            <img src="{{ $picture }}" alt="{{ $user->first_name }} {{ $user->last_name }}" class="profile-avatar">
            <div>
                <h2 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p class="mb-2 opacity-75">{{ '@'.$user->username }}</p>
                <span class="badge rounded-pill text-bg-light text-primary px-3 py-2">{{ $roleLabel }}</span>
            </div>
        </div>
        <div class="card-body p-4">
            <h5 class="profile-section-title">Account Information</h5>
            <div class="row g-3">
                @foreach([
                    ['Full Name', trim($user->first_name.' '.$user->last_name)],
                    ['Username', $user->username],
                    ['Email', $user->email],
                    ['Phone Number', $user->phone ?: 'Not provided'],
                    ['Address', $user->address ?: 'Not provided'],
                    ['User Role', $roleLabel],
                    ['Account Status', ucfirst($user->status)],
                    ['Created Date', $user->created_at?->format('M d, Y')],
                ] as [$label, $value])
                    <div class="col-md-6 col-lg-4"><div class="profile-detail"><small>{{ $label }}</small><strong>{{ $value }}</strong></div></div>
                @endforeach
            </div>
        </div>
    </div>

    @if($teacher)
    <div class="card profile-card mb-4"><div class="card-body p-4">
        <h5 class="profile-section-title"><i class="bi bi-person-video3 me-2 text-primary"></i>Teacher Information</h5>
        <div class="row g-3">
            <div class="col-md-4"><div class="profile-detail"><small>Employee ID</small><strong>{{ $teacher->employee_number }}</strong></div></div>
            <div class="col-md-4"><div class="profile-detail"><small>Assigned Classes</small><strong>{{ $teacher->teachingAssignments->pluck('schoolClass.name')->filter()->unique()->join(', ') ?: 'Not assigned' }}</strong></div></div>
            <div class="col-md-4"><div class="profile-detail"><small>Assigned Subjects</small><strong>{{ $teacher->teachingAssignments->pluck('subject.name')->filter()->unique()->join(', ') ?: 'Not assigned' }}</strong></div></div>
        </div>
    </div></div>
    @endif

    @if($student)
    @php($currentAssignment = $student->classAssignments->first())
    <div class="card profile-card mb-4"><div class="card-body p-4">
        <h5 class="profile-section-title"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Student Information</h5>
        <div class="row g-3">
            <div class="col-md-3"><div class="profile-detail"><small>Admission Number</small><strong>{{ $student->admission_number }}</strong></div></div>
            <div class="col-md-3"><div class="profile-detail"><small>Class</small><strong>{{ $student->schoolClass?->name ?? 'Not assigned' }}</strong></div></div>
            <div class="col-md-3"><div class="profile-detail"><small>Section</small><strong>{{ $student->schoolSection?->name ?? 'Not assigned' }}</strong></div></div>
            <div class="col-md-3"><div class="profile-detail"><small>Academic Year</small><strong>{{ $currentAssignment?->academic_year ?? 'Not assigned' }}</strong></div></div>
        </div>
    </div></div>
    @endif
</div>
@endsection
