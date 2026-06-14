@extends('layouts.super_admin', ['pageTitle' => 'Examination Management'])

@section('super_admin_content')
<div class="page-heading">
    <div><h1>Exam List</h1><p>Review and manage school examinations.</p></div>
    @if ($isManager)<a href="{{ route('examinations.create') }}" class="btn btn-academic"><i class="bi bi-plus-lg me-2"></i>Add Exam</a>@endif
</div>

<form method="GET" action="{{ route('examinations.index') }}" class="row g-2 mb-4">
    <div class="col-lg-3"><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search exam name"></div>
    <div class="col-lg-2"><select name="class_id" class="form-select"><option value="">All classes</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>{{ $class->name }}</option>@endforeach</select></div>
    <div class="col-lg-2"><select name="exam_type_id" class="form-select"><option value="">All exam types</option>@foreach ($examTypes as $type)<option value="{{ $type->id }}" @selected((string) request('exam_type_id') === (string) $type->id)>{{ $type->name }}</option>@endforeach</select></div>
    <div class="col-lg-2"><select name="academic_year" class="form-select"><option value="">All years</option>@foreach ($academicYears as $year)<option value="{{ $year }}" @selected(request('academic_year') === $year)>{{ $year }}</option>@endforeach</select></div>
    <div class="col-lg-1"><select name="status" class="form-select"><option value="">Status</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-lg-2 d-flex gap-2"><button class="btn btn-academic flex-grow-1">Filter</button><a href="{{ route('examinations.index') }}" class="btn btn-outline-secondary">Clear</a></div>
</form>

<div class="table-responsive">
<table class="table table-hover">
    <thead><tr><th>Exam Name</th><th>Type</th><th>Class</th><th>Section</th><th>Academic Year</th><th>Start</th><th>End</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    @forelse ($exams as $exam)
        <tr>
            <td class="fw-semibold">{{ $exam->exam_name }}</td><td>{{ $exam->examType->name }}</td><td>{{ $exam->schoolClass->name }}</td><td>{{ $exam->schoolSection->name }}</td>
            <td>{{ $exam->academic_year }}</td><td>{{ $exam->start_date->format('M d, Y') }}</td><td>{{ $exam->end_date->format('M d, Y') }}</td>
            <td><span class="status-badge status-{{ $exam->status }}">{{ $statuses[$exam->status] }}</span></td>
            <td><div class="d-flex justify-content-end gap-1">
                <a href="{{ route('examinations.schedules.index', ['exam_id' => $exam->id]) }}" class="btn btn-sm btn-outline-primary" title="Schedule"><i class="bi bi-calendar-week"></i></a>
                @if ($isManager)
                    <a href="{{ route('examinations.edit', $exam) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('examinations.destroy', $exam) }}" onsubmit="return confirm('Delete this exam and its schedule?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button></form>
                @endif
            </div></td>
        </tr>
    @empty
        <tr><td colspan="9"><div class="empty-state"><i class="bi bi-clipboard2-x"></i><h5>No exams found</h5><p class="mb-0">Add an exam or adjust the filters.</p></div></td></tr>
    @endforelse
    </tbody>
</table>
</div>
@if ($exams->hasPages())<div class="mt-4">{{ $exams->links() }}</div>@endif
@endsection
