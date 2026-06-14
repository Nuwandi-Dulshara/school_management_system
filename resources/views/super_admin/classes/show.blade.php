@extends('layouts.super_admin', ['pageTitle' => 'Class Details'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Class Details</h1>
        <p>Capacity, sections, and enrollment summary for {{ $schoolClass->name }}.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('super_admin.classes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <a href="{{ route('super_admin.classes.edit', $schoolClass) }}" class="btn btn-academic"><i class="bi bi-pencil me-2"></i>Edit Class</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="role-card">
            <div class="detail-label">Class Name</div>
            <p class="detail-value fs-5">{{ $schoolClass->name }}</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="role-card">
            <div class="detail-label">Student Capacity</div>
            <p class="detail-value fs-5">{{ $schoolClass->students_count }} / {{ $schoolClass->capacity }}</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="role-card">
            <div class="detail-label">Status</div>
            <p class="mb-0"><span class="status-badge status-{{ $schoolClass->status }}">{{ $statuses[$schoolClass->status] }}</span></p>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="text-navy fw-bold mb-0">Sections</h5>
    <a href="{{ route('super_admin.sections.create', ['class_id' => $schoolClass->id]) }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Section
    </a>
</div>
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Section</th><th>Capacity</th><th>Students</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($schoolClass->sections as $section)
                <tr>
                    <td class="fw-semibold">{{ $section->name }}</td>
                    <td>{{ $section->capacity }}</td>
                    <td>{{ $section->students_count }} / {{ $section->capacity }}</td>
                    <td><span class="status-badge status-{{ $section->status }}">{{ $statuses[$section->status] }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('super_admin.sections.edit', $section) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No sections have been created for this class.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<a href="{{ route('super_admin.classes.students', $schoolClass) }}" class="btn btn-academic mt-3">
    <i class="bi bi-people me-2"></i>Manage Class Students
</a>
@endsection
