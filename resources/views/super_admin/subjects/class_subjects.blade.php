@extends('layouts.super_admin', ['pageTitle' => 'Class Subject List'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Class Subject List</h1>
        <p>Assign subjects to classes and review subjects grouped by class.</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@if ($classes->isEmpty() || $subjects->isEmpty())
    <div class="alert alert-warning">
        At least one class and one subject are required before creating assignments.
    </div>
@else
    <div class="border rounded-3 p-4 mb-4">
        <h5 class="text-navy fw-bold mb-3">{{ $editAssignment ? 'Edit Class Subject Assignment' : 'Assign Subject to Class' }}</h5>
        <form method="POST" action="{{ $editAssignment
            ? route('super_admin.subjects.classes.update', $editAssignment)
            : route('super_admin.subjects.classes.store') }}">
            @csrf
            @if ($editAssignment) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="school_class_id" class="form-label fw-semibold">Class</label>
                    <select name="school_class_id" id="school_class_id" class="form-select" required>
                        <option value="">Select a class</option>
                        @foreach ($classes as $schoolClass)
                            <option value="{{ $schoolClass->id }}" @selected((string) old('school_class_id', $editAssignment?->school_class_id) === (string) $schoolClass->id)>
                                {{ $schoolClass->name }}{{ $schoolClass->status === 'inactive' ? ' (Inactive)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="subject_id" class="form-label fw-semibold">Subject</label>
                    <select name="subject_id" id="subject_id" class="form-select" required>
                        <option value="">Select a subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) old('subject_id', $editAssignment?->subject_id) === (string) $subject->id)>
                                {{ $subject->code }} - {{ $subject->name }}{{ $subject->status === 'inactive' ? ' (Inactive)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $editAssignment?->status ?? 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>{{ $editAssignment ? 'Update Assignment' : 'Assign Subject' }}</button>
                @if ($editAssignment)<a href="{{ route('super_admin.subjects.classes') }}" class="btn btn-outline-secondary">Cancel Edit</a>@endif
            </div>
        </form>
    </div>
@endif

<form method="GET" action="{{ route('super_admin.subjects.classes') }}" class="row g-2 mb-4">
    <div class="col-lg-4">
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Subject name or code"></div>
    </div>
    <div class="col-sm-4 col-lg-3">
        <select name="class_id" class="form-select"><option value="">All classes</option>@foreach ($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected((string) request('class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach</select>
    </div>
    <div class="col-sm-4 col-lg-3">
        <select name="status" class="form-select"><option value="">All statuses</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
    </div>
    <div class="col-sm-4 col-lg-2 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1">Filter</button>
        @if (request()->hasAny(['search', 'class_id', 'status']))<a href="{{ route('super_admin.subjects.classes') }}" class="btn btn-outline-secondary">Clear</a>@endif
    </div>
</form>

@forelse ($assignmentsByClass as $classAssignments)
    <div class="border rounded-3 mb-4 overflow-hidden">
        <div class="bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="text-navy fw-bold mb-0">{{ $classAssignments->first()->schoolClass->name }}</h5>
            <span class="role-badge">{{ $classAssignments->count() }} subjects</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Subject Code</th><th>Subject Name</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach ($classAssignments as $assignment)
                        <tr>
                            <td>{{ $assignment->subject->code }}</td>
                            <td><a href="{{ route('super_admin.subjects.show', $assignment->subject) }}" class="fw-semibold text-navy text-decoration-none">{{ $assignment->subject->name }}</a></td>
                            <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('super_admin.subjects.classes', ['edit' => $assignment->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('super_admin.subjects.classes.destroy', $assignment) }}" onsubmit="return confirm('Remove this subject from the class?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="empty-state"><i class="bi bi-journals"></i><h5>No class subject assignments</h5><p class="mb-0">Assign a subject to a class using the form above.</p></div>
@endforelse
@endsection
