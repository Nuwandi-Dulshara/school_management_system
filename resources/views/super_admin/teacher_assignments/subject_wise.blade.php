@extends('layouts.super_admin', ['pageTitle' => 'Subject-wise Teacher List'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Subject-wise Teacher List</h1><p>View teaching responsibility and class coverage grouped by subject.</p></div></div>

<form method="GET" action="{{ route('super_admin.teacher_assignments.subject_wise') }}" class="row g-2 mb-4">
    <div class="col-md-5"><select name="subject_id" class="form-select"><option value="">All subjects</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->code }} - {{ $subject->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-4 d-flex gap-2"><button class="btn btn-academic">Filter</button><a href="{{ route('super_admin.teacher_assignments.subject_wise') }}" class="btn btn-outline-secondary">Clear</a></div>
</form>

@forelse ($assignmentsBySubject as $subjectAssignments)
    <div class="border rounded-3 mb-4 overflow-hidden">
        <div class="bg-light p-3 border-bottom d-flex justify-content-between"><h5 class="text-navy fw-bold mb-0">{{ $subjectAssignments->first()->subject->code }} - {{ $subjectAssignments->first()->subject->name }}</h5><span class="role-badge">{{ $subjectAssignments->count() }} responsibilities</span></div>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Teacher</th><th>Class</th><th>Employee Number</th><th>Contact</th><th>Status</th><th class="text-end">View</th></tr></thead>
            <tbody>@foreach ($subjectAssignments as $assignment)<tr>
                <td><a href="{{ route('super_admin.teachers.show', $assignment->teacher) }}" class="fw-semibold text-navy text-decoration-none">{{ $assignment->teacher->full_name }}</a></td>
                <td>{{ $assignment->schoolClass->name }}</td><td>{{ $assignment->teacher->employee_number }}</td><td>{{ $assignment->teacher->contact_number }}</td>
                <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                <td class="text-end"><a href="{{ route('super_admin.teacher_assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
            </tr>@endforeach</tbody>
        </table></div>
    </div>
@empty
    <div class="empty-state"><i class="bi bi-journal-text"></i><h5>No subject assignments found</h5><p class="mb-0">Assign teachers to subjects first.</p></div>
@endforelse
@endsection
