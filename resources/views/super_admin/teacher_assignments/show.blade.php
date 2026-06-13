@extends('layouts.super_admin', ['pageTitle' => 'Teacher Assignment Details'])

@section('super_admin_content')
<div class="page-heading">
    <div><h1>Teacher Assignment Details</h1><p>Teaching responsibility for the selected class and subject.</p></div>
    <div class="d-flex gap-2"><a href="{{ route('super_admin.teacher_assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a><a href="{{ route('super_admin.teacher_assignments.create', ['edit' => $assignment->id]) }}" class="btn btn-academic"><i class="bi bi-pencil me-2"></i>Edit</a></div>
</div>

<div class="border rounded-3 p-4 mb-4"><div class="row g-4">
    <div class="col-md-4"><div class="detail-label">Teacher</div><p class="detail-value"><a href="{{ route('super_admin.teachers.show', $assignment->teacher) }}" class="text-navy text-decoration-none">{{ $assignment->teacher->full_name }}</a></p></div>
    <div class="col-md-4"><div class="detail-label">Employee Number</div><p class="detail-value">{{ $assignment->teacher->employee_number }}</p></div>
    <div class="col-md-4"><div class="detail-label">Status</div><p class="mb-0"><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></p></div>
    <div class="col-md-4"><div class="detail-label">Class</div><p class="detail-value"><a href="{{ route('super_admin.classes.show', $assignment->schoolClass) }}" class="text-navy text-decoration-none">{{ $assignment->schoolClass->name }}</a></p></div>
    <div class="col-md-4"><div class="detail-label">Subject</div><p class="detail-value"><a href="{{ route('super_admin.subjects.show', $assignment->subject) }}" class="text-navy text-decoration-none">{{ $assignment->subject->code }} - {{ $assignment->subject->name }}</a></p></div>
    <div class="col-md-4"><div class="detail-label">Created Date</div><p class="detail-value">{{ $assignment->created_at->format('F j, Y') }}</p></div>
    <div class="col-md-6"><div class="detail-label">Contact Number</div><p class="detail-value">{{ $assignment->teacher->contact_number }}</p></div>
    <div class="col-md-6"><div class="detail-label">Email</div><p class="detail-value">{{ $assignment->teacher->email }}</p></div>
</div></div>

<div class="d-flex gap-2">
    <form method="POST" action="{{ route('super_admin.teacher_assignments.status', $assignment) }}">@csrf @method('PATCH')<button class="btn {{ $assignment->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $assignment->status === 'active' ? 'Deactivate Assignment' : 'Activate Assignment' }}</button></form>
    <form method="POST" action="{{ route('super_admin.teacher_assignments.destroy', $assignment) }}" onsubmit="return confirm('Remove this teaching assignment permanently?');">@csrf @method('DELETE')<button class="btn btn-outline-danger">Remove Assignment</button></form>
</div>
@endsection
