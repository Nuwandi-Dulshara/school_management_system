@extends('layouts.super_admin', ['pageTitle' => 'Teacher Assignment List'])

@section('super_admin_content')
<div class="page-heading">
    <div><h1>Teacher Assignment List</h1><p>Search, filter, update, deactivate, or remove teaching responsibilities.</p></div>
    <a href="{{ route('super_admin.teacher_assignments.create') }}" class="btn btn-academic"><i class="bi bi-plus-lg me-2"></i>Assign Teacher</a>
</div>

<form method="GET" action="{{ route('super_admin.teacher_assignments.index') }}" class="row g-2 mb-4">
    <div class="col-xl-3 col-md-6"><div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Teacher, subject, or class"></div></div>
    <div class="col-xl-2 col-md-3"><select name="teacher_id" class="form-select"><option value="">All teachers</option>@foreach ($teachers as $teacher)<option value="{{ $teacher->id }}" @selected((string) request('teacher_id') === (string) $teacher->id)>{{ $teacher->full_name }}</option>@endforeach</select></div>
    <div class="col-xl-2 col-md-3"><select name="class_id" class="form-select"><option value="">All classes</option>@foreach ($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected((string) request('class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach</select></div>
    <div class="col-xl-2 col-md-4"><select name="subject_id" class="form-select"><option value="">All subjects</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
    <div class="col-xl-1 col-md-4"><select name="status" class="form-select"><option value="">Status</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-xl-2 col-md-4 d-flex gap-2"><button class="btn btn-academic flex-grow-1">Filter</button>@if (request()->hasAny(['search', 'teacher_id', 'class_id', 'subject_id', 'status']))<a href="{{ route('super_admin.teacher_assignments.index') }}" class="btn btn-outline-secondary">Clear</a>@endif</div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Teacher</th><th>Contact</th><th>Class</th><th>Subject</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($assignments as $assignment)
                <tr>
                    <td><div class="fw-semibold text-navy">{{ $assignment->teacher->full_name }}</div><small class="text-muted">{{ $assignment->teacher->employee_number }}</small></td>
                    <td><div>{{ $assignment->teacher->contact_number }}</div><small class="text-muted">{{ $assignment->teacher->email }}</small></td>
                    <td>{{ $assignment->schoolClass->name }}</td>
                    <td>{{ $assignment->subject->code }} - {{ $assignment->subject->name }}</td>
                    <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                    <td><div class="d-flex justify-content-end gap-1">
                        <a href="{{ route('super_admin.teacher_assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('super_admin.teacher_assignments.create', ['edit' => $assignment->id]) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('super_admin.teacher_assignments.status', $assignment) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $assignment->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="Activate or deactivate"><i class="bi {{ $assignment->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i></button></form>
                        <form method="POST" action="{{ route('super_admin.teacher_assignments.destroy', $assignment) }}" onsubmit="return confirm('Remove this teaching assignment permanently?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash"></i></button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-person-workspace"></i><h5>No assignments found</h5><p class="mb-0">Adjust the filters or assign a teacher.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($assignments->hasPages())<div class="mt-4">{{ $assignments->links() }}</div>@endif
@endsection
