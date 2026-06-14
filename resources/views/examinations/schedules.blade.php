@extends('layouts.super_admin', ['pageTitle' => 'Exam Schedule'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Exam Schedule</h1><p>Build and review the full examination timetable.</p></div></div>

@if ($isManager)
<div class="card border-0 bg-light mb-4"><div class="card-body">
    <h5 class="text-navy mb-3">{{ $editSchedule ? 'Edit Schedule Record' : 'Add Subject to Exam' }}</h5>
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ $editSchedule ? route('examinations.schedules.update', $editSchedule) : route('examinations.schedules.store') }}" class="row g-3">
        @csrf @if ($editSchedule) @method('PUT') @endif
        <div class="col-lg-4"><label class="form-label fw-semibold">Exam</label><select name="exam_id" class="form-select" required><option value="">Select exam</option>@foreach ($exams as $exam)<option value="{{ $exam->id }}" @selected((string) old('exam_id', $editSchedule->exam_id ?? request('exam_id')) === (string) $exam->id)>{{ $exam->exam_name }} - {{ $exam->schoolClass->name }} {{ $exam->schoolSection->name }}</option>@endforeach</select></div>
        <div class="col-lg-3"><label class="form-label fw-semibold">Subject</label><select name="subject_id" class="form-select" required><option value="">Select subject</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}" @selected((string) old('subject_id', $editSchedule->subject_id ?? '') === (string) $subject->id)>{{ $subject->code }} - {{ $subject->name }}</option>@endforeach</select></div>
        <div class="col-lg-2"><label class="form-label fw-semibold">Exam Date</label><input type="date" name="exam_date" class="form-control" value="{{ old('exam_date', $editSchedule?->exam_date?->toDateString()) }}" required></div>
        <div class="col-lg-1"><label class="form-label fw-semibold">Start</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time', $editSchedule?->start_time ? substr($editSchedule->start_time, 0, 5) : '') }}" required></div>
        <div class="col-lg-1"><label class="form-label fw-semibold">End</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time', $editSchedule?->end_time ? substr($editSchedule->end_time, 0, 5) : '') }}" required></div>
        <div class="col-lg-1"><label class="form-label fw-semibold">Room</label><input type="text" name="room" class="form-control" value="{{ old('room', $editSchedule->room ?? '') }}"></div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-academic">{{ $editSchedule ? 'Update Schedule' : 'Add Schedule' }}</button>@if ($editSchedule)<a href="{{ route('examinations.schedules.index', ['exam_id' => request('exam_id')]) }}" class="btn btn-outline-secondary">Cancel</a>@endif</div>
    </form>
</div></div>
@endif

<form method="GET" class="row g-2 mb-4"><div class="col-md-8"><select name="exam_id" class="form-select"><option value="">All exams</option>@foreach ($exams as $exam)<option value="{{ $exam->id }}" @selected((string) request('exam_id') === (string) $exam->id)>{{ $exam->exam_name }} - {{ $exam->schoolClass->name }} {{ $exam->schoolSection->name }}</option>@endforeach</select></div><div class="col-md-4 d-flex gap-2"><button class="btn btn-academic flex-grow-1">Filter Timetable</button><a href="{{ route('examinations.schedules.index') }}" class="btn btn-outline-secondary">Clear</a></div></form>
<div class="table-responsive"><table class="table table-hover">
    <thead><tr><th>Exam</th><th>Class / Section</th><th>Subject</th><th>Date</th><th>Time</th><th>Room / Hall</th>@if ($isManager)<th class="text-end">Actions</th>@endif</tr></thead>
    <tbody>@forelse ($schedules as $schedule)<tr><td class="fw-semibold">{{ $schedule->exam->exam_name }}</td><td>{{ $schedule->exam->schoolClass->name }} / {{ $schedule->exam->schoolSection->name }}</td><td>{{ $schedule->subject->name }}</td><td>{{ $schedule->exam_date->format('M d, Y') }}</td><td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</td><td>{{ $schedule->room ?: 'Not assigned' }}</td>@if ($isManager)<td><div class="d-flex justify-content-end gap-1"><a href="{{ route('examinations.schedules.index', ['exam_id' => request('exam_id'), 'edit' => $schedule->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route('examinations.schedules.destroy', $schedule) }}" onsubmit="return confirm('Delete this schedule record?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></div></td>@endif</tr>@empty<tr><td colspan="{{ $isManager ? 7 : 6 }}"><div class="empty-state"><i class="bi bi-calendar-week"></i><h5>No schedule records found</h5></div></td></tr>@endforelse</tbody>
</table></div>
@if ($schedules->hasPages())<div class="mt-4">{{ $schedules->links() }}</div>@endif
@endsection
