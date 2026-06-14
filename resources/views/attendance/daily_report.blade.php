@extends('layouts.super_admin', ['pageTitle' => 'Daily Attendance Report'])

@section('super_admin_content')
<div class="page-heading">
    <div><h1>Daily Attendance Report</h1><p>View attendance totals across all classes and sections for one day.</p></div>
</div>
@include('attendance._alerts')

<form method="GET" action="{{ route('attendance.daily_report') }}" class="row g-3 align-items-end mb-4">
    <div class="col-md-5"><label for="daily_date" class="form-label fw-semibold">Date</label><input type="date" name="date" id="daily_date" class="form-control" value="{{ $date }}" required></div>
    <div class="col-md-3"><button class="btn btn-academic w-100">View Report</button></div>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 bg-success-subtle"><div class="card-body"><div class="text-muted">Present</div><div class="fs-3 fw-bold text-success">{{ $report->sum('present_count') }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 bg-danger-subtle"><div class="card-body"><div class="text-muted">Absent</div><div class="fs-3 fw-bold text-danger">{{ $report->sum('absent_count') }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 bg-warning-subtle"><div class="card-body"><div class="text-muted">Late</div><div class="fs-3 fw-bold text-warning-emphasis">{{ $report->sum('late_count') }}</div></div></div></div>
</div>

<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Class</th><th>Section</th><th>Present</th><th>Absent</th><th>Late</th><th>Total</th></tr></thead><tbody>
    @forelse ($report as $row)<tr><td>{{ $row->schoolClass->name }}</td><td>{{ $row->schoolSection->name }}</td><td>{{ $row->present_count }}</td><td>{{ $row->absent_count }}</td><td>{{ $row->late_count }}</td><td class="fw-semibold">{{ $row->present_count + $row->absent_count + $row->late_count }}</td></tr>
    @empty <tr><td colspan="6"><div class="empty-state"><i class="bi bi-calendar-x"></i><h5>No attendance found</h5><p class="mb-0">No attendance records were saved for {{ \Illuminate\Support\Carbon::parse($date)->format('M d, Y') }}.</p></div></td></tr>@endforelse
</tbody></table></div>
@endsection
