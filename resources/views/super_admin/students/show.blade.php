@extends('layouts.super_admin', ['pageTitle' => 'Student Profile'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Student Profile</h1>
        <p>Student, admission, and parent or guardian information.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('super_admin.students.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <a href="{{ route('super_admin.students.edit', $student) }}" class="btn btn-academic">
            <i class="bi bi-pencil me-2"></i>Edit Student
        </a>
    </div>
</div>

<div class="border rounded-3 p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <span class="user-avatar" style="width: 64px; height: 64px; font-size: 1.2rem;">
            {{ strtoupper(substr($student->full_name, 0, 2)) }}
        </span>
        <div>
            <h4 class="text-navy fw-bold mb-1">{{ $student->full_name }}</h4>
            <div class="d-flex gap-2 align-items-center">
                <span class="role-badge">{{ $student->admission_number }}</span>
                <span class="status-badge status-{{ $student->status }}">{{ $statuses[$student->status] }}</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4"><div class="detail-label">Full Name</div><p class="detail-value">{{ $student->full_name }}</p></div>
        <div class="col-md-4"><div class="detail-label">Admission Number</div><p class="detail-value">{{ $student->admission_number }}</p></div>
        <div class="col-md-4"><div class="detail-label">Date of Birth</div><p class="detail-value">{{ $student->date_of_birth->format('F j, Y') }}</p></div>
        <div class="col-md-4"><div class="detail-label">Gender</div><p class="detail-value">{{ $genders[$student->gender] ?? ucfirst($student->gender) }}</p></div>
        <div class="col-md-4"><div class="detail-label">Contact Number</div><p class="detail-value">{{ $student->contact_number }}</p></div>
        <div class="col-md-4"><div class="detail-label">Email</div><p class="detail-value">{{ $student->email ?: 'Not provided' }}</p></div>
        <div class="col-md-4"><div class="detail-label">Class</div><p class="detail-value">{{ $student->class }}</p></div>
        <div class="col-md-4"><div class="detail-label">Section</div><p class="detail-value">{{ $student->section }}</p></div>
        <div class="col-md-4"><div class="detail-label">Admission Date</div><p class="detail-value">{{ $student->admission_date->format('F j, Y') }}</p></div>
        <div class="col-md-4"><div class="detail-label">Status</div><p class="detail-value">{{ $statuses[$student->status] }}</p></div>
        <div class="col-md-4"><div class="detail-label">Created Date</div><p class="detail-value">{{ $student->created_at->format('F j, Y') }}</p></div>
        <div class="col-12"><div class="detail-label">Address</div><p class="detail-value">{{ $student->address }}</p></div>
    </div>
</div>

<div class="border rounded-3 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="text-navy fw-bold mb-0"><i class="bi bi-people me-2"></i>Parent / Guardian Details</h5>
        <a href="{{ route('super_admin.students.guardians', $student) }}" class="btn btn-sm btn-outline-primary">Manage</a>
    </div>
    @if ($student->guardian)
        <div class="row g-4">
            <div class="col-md-4"><div class="detail-label">Name</div><p class="detail-value">{{ $student->guardian->guardian_name }}</p></div>
            <div class="col-md-4"><div class="detail-label">Relationship</div><p class="detail-value">{{ $student->guardian->relationship }}</p></div>
            <div class="col-md-4"><div class="detail-label">Contact Number</div><p class="detail-value">{{ $student->guardian->contact_number }}</p></div>
            <div class="col-md-4"><div class="detail-label">Email</div><p class="detail-value">{{ $student->guardian->email ?: 'Not provided' }}</p></div>
            <div class="col-12"><div class="detail-label">Address</div><p class="detail-value">{{ $student->guardian->address }}</p></div>
        </div>
    @else
        <p class="text-muted mb-0">No parent or guardian details are available.</p>
    @endif
</div>
@endsection
