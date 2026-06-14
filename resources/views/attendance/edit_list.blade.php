@extends('layouts.super_admin', ['pageTitle' => 'Edit Attendance'])

@section('super_admin_content')
<div class="page-heading">
    <div><h1>Edit Attendance</h1><p>Find an attendance entry and update its status.</p></div>
</div>
@include('attendance._alerts')

<form method="GET" action="{{ route('attendance.edit_list') }}" class="row g-3 align-items-end mb-4">
    @include('attendance._filters')
    <div class="col-xl-3 col-md-6 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1">Filter</button>
        @if (request()->hasAny(['date', 'class_id', 'section_id']))<a href="{{ route('attendance.edit_list') }}" class="btn btn-outline-secondary">Clear</a>@endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Date</th><th>Student</th><th>Class</th><th>Section</th><th>Current Status</th><th class="text-end">Edit</th></tr></thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->attendance_date->format('M d, Y') }}</td>
                    <td><div class="fw-semibold text-navy">{{ $attendance->student->full_name }}</div><small class="text-muted">{{ $attendance->student->admission_number }}</small></td>
                    <td>{{ $attendance->schoolClass->name }}</td>
                    <td>{{ $attendance->schoolSection->name }}</td>
                    <td><span class="status-badge status-attendance-{{ $attendance->status }}">{{ $statuses[$attendance->status] }}</span></td>
                    <td class="text-end"><a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-pencil-square"></i><h5>No attendance records found</h5><p class="mb-0">Use the filters to find an entry.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($attendances->hasPages())<div class="mt-4">{{ $attendances->links() }}</div>@endif
@endsection

@section('scripts')
@parent
@include('attendance._class_section_script')
@endsection
