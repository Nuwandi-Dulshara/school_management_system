@extends('layouts.super_admin', ['pageTitle' => 'Student Attendance History'])

@section('super_admin_content')
<div class="page-heading">
    <div><h1>Student Attendance History</h1><p>Search for a student and review their full attendance record.</p></div>
</div>
@include('attendance._alerts')

<form method="GET" action="{{ route('attendance.student_history') }}" class="row g-3 align-items-end mb-4">
    <div class="col-lg-4">
        <label for="student_search" class="form-label fw-semibold">Search Student</label>
        <input type="search" name="search" id="student_search" class="form-control" value="{{ request('search') }}" placeholder="Name or admission number">
    </div>
    <div class="col-lg-5">
        <label for="student_id" class="form-label fw-semibold">Student</label>
        <select name="student_id" id="student_id" class="form-select">
            <option value="">Select student</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected((string) request('student_id') === (string) $student->id)>{{ $student->full_name }} ({{ $student->admission_number }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1">View History</button>
        @if (request()->hasAny(['search', 'student_id']))<a href="{{ route('attendance.student_history') }}" class="btn btn-outline-secondary">Clear</a>@endif
    </div>
</form>

@if ($selectedStudent)
    <div class="alert alert-light border"><strong>{{ $selectedStudent->full_name }}</strong> <span class="text-muted">({{ $selectedStudent->admission_number }})</span></div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Date</th><th>Class</th><th>Section</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($history as $attendance)
                    <tr><td>{{ $attendance->attendance_date->format('M d, Y') }}</td><td>{{ $attendance->schoolClass->name }}</td><td>{{ $attendance->schoolSection->name }}</td><td><span class="status-badge status-attendance-{{ $attendance->status }}">{{ $statuses[$attendance->status] }}</span></td></tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state"><i class="bi bi-clock-history"></i><h5>No history found</h5><p class="mb-0">This student has no saved attendance records.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($history->hasPages())<div class="mt-4">{{ $history->links() }}</div>@endif
@endif
@endsection
