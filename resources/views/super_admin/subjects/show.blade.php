@extends('layouts.super_admin', ['pageTitle' => 'Subject Details'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Subject Details</h1>
        <p>Subject information and assigned classes.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('super_admin.subjects.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <a href="{{ route('super_admin.subjects.edit', $subject) }}" class="btn btn-academic"><i class="bi bi-pencil me-2"></i>Edit Subject</a>
    </div>
</div>

<div class="border rounded-3 p-4 mb-4">
    <div class="row g-4">
        <div class="col-md-4"><div class="detail-label">Subject Code</div><p class="detail-value">{{ $subject->code }}</p></div>
        <div class="col-md-4"><div class="detail-label">Subject Name</div><p class="detail-value">{{ $subject->name }}</p></div>
        <div class="col-md-4"><div class="detail-label">Status</div><p class="mb-0"><span class="status-badge status-{{ $subject->status }}">{{ $statuses[$subject->status] }}</span></p></div>
        <div class="col-md-4"><div class="detail-label">Created Date</div><p class="detail-value">{{ $subject->created_at->format('F j, Y') }}</p></div>
        <div class="col-12"><div class="detail-label">Description</div><p class="detail-value">{{ $subject->description ?: 'No description provided.' }}</p></div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="text-navy fw-bold mb-0">Assigned Classes</h5>
    <a href="{{ route('super_admin.subjects.classes') }}" class="btn btn-sm btn-outline-primary">Manage Assignments</a>
</div>
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Class</th><th>Assignment Status</th></tr></thead>
        <tbody>
            @forelse ($subject->classAssignments as $assignment)
                <tr>
                    <td><a href="{{ route('super_admin.classes.show', $assignment->schoolClass) }}" class="fw-semibold text-navy text-decoration-none">{{ $assignment->schoolClass->name }}</a></td>
                    <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center text-muted py-4">This subject is not assigned to any classes.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
