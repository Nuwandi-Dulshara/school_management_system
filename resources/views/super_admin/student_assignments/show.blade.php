@extends('layouts.super_admin', ['pageTitle' => 'Assignment Details'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Assignment Details</h1>
        <p>Student, class, section, and academic year assignment information.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('super_admin.student_assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @if ($assignment->status === 'assigned')
            <a href="{{ route('super_admin.student_assignments.transfers', ['assignment' => $assignment->id]) }}" class="btn btn-outline-warning"><i class="bi bi-arrow-left-right me-2"></i>Transfer</a>
            <a href="{{ route('super_admin.student_assignments.create', ['edit' => $assignment->id]) }}" class="btn btn-academic"><i class="bi bi-pencil me-2"></i>Edit</a>
        @endif
    </div>
</div>

<div class="border rounded-3 p-4">
    <div class="row g-4">
        <div class="col-md-4"><div class="detail-label">Student ID</div><p class="detail-value">#{{ $assignment->student_id }}</p></div>
        <div class="col-md-4"><div class="detail-label">Full Name</div><p class="detail-value"><a href="{{ route('super_admin.students.show', $assignment->student) }}" class="text-navy text-decoration-none">{{ $assignment->student->full_name }}</a></p></div>
        <div class="col-md-4"><div class="detail-label">Admission Number</div><p class="detail-value">{{ $assignment->student->admission_number }}</p></div>
        <div class="col-md-4"><div class="detail-label">Academic Year</div><p class="detail-value">{{ $assignment->academic_year }}</p></div>
        <div class="col-md-4"><div class="detail-label">Class</div><p class="detail-value"><a href="{{ route('super_admin.classes.show', $assignment->schoolClass) }}" class="text-navy text-decoration-none">{{ $assignment->schoolClass->name }}</a></p></div>
        <div class="col-md-4"><div class="detail-label">Section</div><p class="detail-value">{{ $assignment->schoolSection->name }}</p></div>
        <div class="col-md-4"><div class="detail-label">Status</div><p class="mb-0"><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></p></div>
        <div class="col-md-4"><div class="detail-label">Assigned Date</div><p class="detail-value">{{ $assignment->assigned_at?->format('F j, Y g:i A') ?? 'Not recorded' }}</p></div>
        <div class="col-md-4"><div class="detail-label">Last Updated</div><p class="detail-value">{{ $assignment->updated_at->format('F j, Y g:i A') }}</p></div>
        @if ($assignment->transferred_at)<div class="col-md-4"><div class="detail-label">Transferred Date</div><p class="detail-value">{{ $assignment->transferred_at->format('F j, Y g:i A') }}</p></div>@endif
        @if ($assignment->removed_at)<div class="col-md-4"><div class="detail-label">Removed Date</div><p class="detail-value">{{ $assignment->removed_at->format('F j, Y g:i A') }}</p></div>@endif
    </div>
</div>
@endsection
