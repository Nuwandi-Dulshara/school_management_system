@extends('layouts.super_admin', ['pageTitle' => 'Class Attendance Report'])

@section('super_admin_content')
<div class="page-heading">
    <div><h1>Class Attendance Report</h1><p>Generate date-wise totals for a class and section.</p></div>
</div>
@include('attendance._alerts')

<form method="GET" action="{{ route('attendance.class_report') }}" class="row g-3 align-items-end mb-4">
    <div class="col-lg-3 col-md-6">
        <label for="report_class_id" class="form-label fw-semibold">Class</label>
        <select name="class_id" id="report_class_id" class="form-select" required>
            <option value="">Select class</option>
            @foreach ($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected((string) request('class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-lg-3 col-md-6">
        <label for="report_section_id" class="form-label fw-semibold">Section</label>
        <select name="section_id" id="report_section_id" class="form-select" required>
            <option value="">Select section</option>
            @foreach ($classes as $schoolClass)@foreach ($schoolClass->sections as $section)<option value="{{ $section->id }}" data-class-id="{{ $schoolClass->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $section->name }}</option>@endforeach @endforeach
        </select>
    </div>
    <div class="col-lg-2 col-md-6"><label for="date_from" class="form-label fw-semibold">From</label><input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}" required></div>
    <div class="col-lg-2 col-md-6"><label for="date_to" class="form-label fw-semibold">To</label><input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}" required></div>
    <div class="col-lg-2"><button class="btn btn-academic w-100">Generate</button></div>
</form>

@if (request()->filled(['class_id', 'section_id', 'date_from', 'date_to']))
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 bg-success-subtle"><div class="card-body"><div class="text-muted">Total Present</div><div class="fs-3 fw-bold text-success">{{ $report->sum('present_count') }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 bg-danger-subtle"><div class="card-body"><div class="text-muted">Total Absent</div><div class="fs-3 fw-bold text-danger">{{ $report->sum('absent_count') }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 bg-warning-subtle"><div class="card-body"><div class="text-muted">Total Late</div><div class="fs-3 fw-bold text-warning-emphasis">{{ $report->sum('late_count') }}</div></div></div></div>
    </div>
    <div class="table-responsive"><table class="table table-hover"><thead><tr><th>Date</th><th>Present</th><th>Absent</th><th>Late</th><th>Total</th></tr></thead><tbody>
        @forelse ($report as $row)<tr><td>{{ \Illuminate\Support\Carbon::parse($row->attendance_date)->format('M d, Y') }}</td><td>{{ $row->present_count }}</td><td>{{ $row->absent_count }}</td><td>{{ $row->late_count }}</td><td class="fw-semibold">{{ $row->present_count + $row->absent_count + $row->late_count }}</td></tr>
        @empty <tr><td colspan="5"><div class="empty-state"><i class="bi bi-bar-chart"></i><h5>No report data</h5><p class="mb-0">No attendance was found for this period.</p></div></td></tr>@endforelse
    </tbody></table></div>
@endif
@endsection

@section('scripts')
@parent
@include('attendance._class_section_script', ['classSelectId' => 'report_class_id', 'sectionSelectId' => 'report_section_id'])
@endsection
