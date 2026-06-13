@extends('layouts.super_admin', ['pageTitle' => 'Assigned Classes'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Assigned Classes</h1>
        <p>Manage class assignments for {{ $teacher->full_name }}.</p>
    </div>
    <a href="{{ route('super_admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Teacher Profile
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="border rounded-3 p-4 mb-4">
    <h5 class="text-navy fw-bold mb-4">{{ $editAssignment ? 'Edit Class Assignment' : 'Add Class Assignment' }}</h5>
    <form method="POST" action="{{ $editAssignment
        ? route('super_admin.teachers.classes.update', [$teacher, $editAssignment])
        : route('super_admin.teachers.classes.store', $teacher) }}">
        @csrf
        @if ($editAssignment) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Teacher Name</label>
                <input type="text" class="form-control" value="{{ $teacher->full_name }}" disabled>
            </div>
            <div class="col-md-2">
                <label for="class_name" class="form-label fw-semibold">Class Name</label>
                <input type="text" name="class_name" id="class_name" class="form-control @error('class_name') is-invalid @enderror"
                    value="{{ old('class_name', $editAssignment?->class_name) }}" required>
                @error('class_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="section" class="form-label fw-semibold">Section</label>
                <input type="text" name="section" id="section" class="form-control @error('section') is-invalid @enderror"
                    value="{{ old('section', $editAssignment?->section) }}" required>
                @error('section')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="academic_year" class="form-label fw-semibold">Academic Year</label>
                <input type="text" name="academic_year" id="academic_year" class="form-control @error('academic_year') is-invalid @enderror"
                    value="{{ old('academic_year', $editAssignment?->academic_year ?? now()->format('Y')) }}" placeholder="2026" required>
                @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $editAssignment?->status ?? 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-academic" type="submit">
                <i class="bi bi-check-lg me-2"></i>{{ $editAssignment ? 'Update Assignment' : 'Add Assignment' }}
            </button>
            @if ($editAssignment)
                <a href="{{ route('super_admin.teachers.classes', $teacher) }}" class="btn btn-outline-secondary">Cancel Edit</a>
            @endif
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Teacher Name</th>
                <th>Class Name</th>
                <th>Section</th>
                <th>Academic Year</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assignments as $assignment)
                <tr>
                    <td class="fw-semibold">{{ $teacher->full_name }}</td>
                    <td>{{ $assignment->class_name }}</td>
                    <td>{{ $assignment->section }}</td>
                    <td>{{ $assignment->academic_year }}</td>
                    <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('super_admin.teachers.classes', [$teacher, 'edit' => $assignment->id]) }}"
                                class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('super_admin.teachers.classes.destroy', [$teacher, $assignment]) }}"
                                onsubmit="return confirm('Remove this class assignment?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-building"></i>
                            <h5>No assigned classes</h5>
                            <p class="mb-0">Add the first class assignment using the form above.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
