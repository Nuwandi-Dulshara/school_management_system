@extends('layouts.super_admin', ['pageTitle' => 'Class-wise Teacher List'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Class-wise Teacher List</h1><p>View teachers and their assigned subjects grouped by class.</p></div></div>

<form method="GET" action="{{ route('super_admin.teacher_assignments.class_wise') }}" class="row g-2 mb-4">
    <div class="col-md-5"><select name="class_id" class="form-select"><option value="">All classes</option>@foreach ($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected((string) request('class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-4 d-flex gap-2"><button class="btn btn-academic">Filter</button><a href="{{ route('super_admin.teacher_assignments.class_wise') }}" class="btn btn-outline-secondary">Clear</a></div>
</form>

@forelse ($assignmentsByClass as $classAssignments)
    <div class="border rounded-3 mb-4 overflow-hidden">
        <div class="bg-light p-3 border-bottom d-flex justify-content-between"><h5 class="text-navy fw-bold mb-0">{{ $classAssignments->first()->schoolClass->name }}</h5><span class="role-badge">{{ $classAssignments->count() }} assignments</span></div>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Teacher</th><th>Subject</th><th>Contact Number</th><th>Email</th><th>Status</th><th class="text-end">View</th></tr></thead>
            <tbody>@foreach ($classAssignments as $assignment)<tr>
                <td><a href="{{ route('super_admin.teachers.show', $assignment->teacher) }}" class="fw-semibold text-navy text-decoration-none">{{ $assignment->teacher->full_name }}</a></td>
                <td>{{ $assignment->subject->code }} - {{ $assignment->subject->name }}</td>
                <td>{{ $assignment->teacher->contact_number }}</td><td>{{ $assignment->teacher->email }}</td>
                <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                <td class="text-end"><a href="{{ route('super_admin.teacher_assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
            </tr>@endforeach</tbody>
        </table></div>
    </div>
@empty
    <div class="empty-state"><i class="bi bi-building"></i><h5>No class assignments found</h5><p class="mb-0">Assign teachers to class subjects first.</p></div>
@endforelse
@endsection
