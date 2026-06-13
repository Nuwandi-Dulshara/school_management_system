@extends('layouts.super_admin', ['pageTitle' => 'Teacher Profile'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Teacher Profile</h1>
        <p>Personal, contact, and professional information.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('super_admin.teachers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <a href="{{ route('super_admin.teachers.edit', $teacher) }}" class="btn btn-academic">
            <i class="bi bi-pencil me-2"></i>Edit Teacher
        </a>
    </div>
</div>

<div class="border rounded-3 p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <span class="user-avatar" style="width: 64px; height: 64px; font-size: 1.2rem;">
            {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
        </span>
        <div>
            <h4 class="text-navy fw-bold mb-1">{{ $teacher->full_name }}</h4>
            <div class="d-flex gap-2 align-items-center">
                <span class="role-badge">{{ $teacher->employee_number }}</span>
                <span class="status-badge status-{{ $teacher->status }}">{{ $statuses[$teacher->status] }}</span>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-4"><div class="detail-label">Full Name</div><p class="detail-value">{{ $teacher->full_name }}</p></div>
        <div class="col-md-4"><div class="detail-label">Employee Number</div><p class="detail-value">{{ $teacher->employee_number }}</p></div>
        <div class="col-md-4"><div class="detail-label">Date of Birth</div><p class="detail-value">{{ $teacher->date_of_birth->format('F j, Y') }}</p></div>
        <div class="col-md-4"><div class="detail-label">Gender</div><p class="detail-value">{{ $genders[$teacher->gender] ?? ucfirst($teacher->gender) }}</p></div>
        <div class="col-md-4"><div class="detail-label">Contact Number</div><p class="detail-value">{{ $teacher->contact_number }}</p></div>
        <div class="col-md-4"><div class="detail-label">Email</div><p class="detail-value">{{ $teacher->email }}</p></div>
        <div class="col-md-4"><div class="detail-label">Qualification</div><p class="detail-value">{{ $teacher->qualification }}</p></div>
        <div class="col-md-4"><div class="detail-label">Experience</div><p class="detail-value">{{ $teacher->experience }} {{ $teacher->experience === 1 ? 'year' : 'years' }}</p></div>
        <div class="col-md-4"><div class="detail-label">Main Subject</div><p class="detail-value">{{ $teacher->main_subject }}</p></div>
        <div class="col-md-4"><div class="detail-label">Joining Date</div><p class="detail-value">{{ $teacher->joining_date->format('F j, Y') }}</p></div>
        <div class="col-md-4"><div class="detail-label">Status</div><p class="detail-value">{{ $statuses[$teacher->status] }}</p></div>
        <div class="col-md-4"><div class="detail-label">Created Date</div><p class="detail-value">{{ $teacher->created_at->format('F j, Y') }}</p></div>
        <div class="col-12"><div class="detail-label">Address</div><p class="detail-value">{{ $teacher->address }}</p></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <a href="{{ route('super_admin.teachers.classes', $teacher) }}" class="text-decoration-none">
            <div class="role-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="role-icon"><i class="bi bi-building"></i></div>
                    <div>
                        <h5 class="text-navy fw-bold mb-1">Assigned Classes</h5>
                        <p class="text-muted mb-0">{{ $teacher->assigned_classes_count }} class assignments</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('super_admin.teachers.subjects', $teacher) }}" class="text-decoration-none">
            <div class="role-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="role-icon"><i class="bi bi-book"></i></div>
                    <div>
                        <h5 class="text-navy fw-bold mb-1">Assigned Subjects</h5>
                        <p class="text-muted mb-0">{{ $teacher->assigned_subjects_count }} subject assignments</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
