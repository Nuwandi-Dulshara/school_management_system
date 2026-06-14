@extends('layouts.super_admin', ['pageTitle' => 'Subject Result Report'])

@section('page_styles')
<style>@media print { .sidebar, .topbar, .report-filters, .print-button { display:none !important; } .main-content { margin:0; padding:0; } .content-area { box-shadow:none; } }</style>
@endsection

@section('super_admin_content')
<div class="page-heading"><div><h1>Subject Result Report</h1><p>Review subject performance and pass/fail totals.</p></div>@if ($marks->isNotEmpty())<button onclick="window.print()" class="btn btn-outline-primary print-button"><i class="bi bi-printer me-2"></i>Print</button>@endif</div>
<form method="GET" class="row g-3 mb-4 report-filters">
    @include('marks._selection_filters')
    <div class="col-md-3"><label class="form-label fw-semibold">Academic Year</label><select name="academic_year" class="form-select"><option value="">All years</option>@foreach ($academicYears as $year)<option value="{{ $year }}" @selected(($filters['academic_year'] ?? '') === $year)>{{ $year }}</option>@endforeach</select></div>
    <div class="col-12"><button class="btn btn-academic">Generate Report</button></div>
</form>
@if ($marks->isNotEmpty())<div class="row g-3 mb-4"><div class="col-md-3"><div class="role-card"><div class="detail-label">Students</div><p class="detail-value">{{ $marks->count() }}</p></div></div><div class="col-md-3"><div class="role-card"><div class="detail-label">Passed</div><p class="detail-value text-success">{{ $summary['pass'] }}</p></div></div><div class="col-md-3"><div class="role-card"><div class="detail-label">Failed</div><p class="detail-value text-danger">{{ $summary['fail'] }}</p></div></div><div class="col-md-3"><div class="role-card"><div class="detail-label">Pass Rate</div><p class="detail-value">{{ number_format(($summary['pass'] / $marks->count()) * 100, 1) }}%</p></div></div></div>@endif
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Student</th><th>Admission No.</th><th>Marks</th><th>Maximum</th><th>Grade</th><th>Remarks</th><th>Status</th></tr></thead><tbody>@forelse ($marks as $mark)<tr><td class="fw-semibold">{{ $mark->student->full_name }}</td><td>{{ $mark->student->admission_number }}</td><td>{{ number_format($mark->marks_obtained, 2) }}</td><td>{{ number_format($mark->maximum_marks, 2) }}</td><td>{{ $mark->grade }}</td><td>{{ $mark->remarks ?: '-' }}</td><td><span class="status-badge status-{{ $mark->status }}">{{ ucfirst($mark->status) }}</span></td></tr>@empty<tr><td colspan="7"><div class="empty-state"><i class="bi bi-journal-text"></i><h5>No subject results found</h5></div></td></tr>@endforelse</tbody></table></div>
@endsection

@section('scripts')
@parent
@include('marks._selection_script')
@endsection
