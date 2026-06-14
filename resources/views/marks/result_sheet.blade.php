@extends('layouts.super_admin', ['pageTitle' => 'Result Sheet'])

@section('page_styles')
<style>@media print { .sidebar, .topbar, .result-filters, .print-button { display: none !important; } .main-content { margin: 0; padding: 0; } .content-area { box-shadow: none; } }</style>
@endsection

@section('super_admin_content')
<div class="page-heading"><div><h1>Result Sheet</h1><p>Generate a complete result sheet for a selected student.</p></div>@if ($result)<button class="btn btn-outline-primary print-button" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print</button>@endif</div>
<form method="GET" class="row g-3 mb-4 result-filters">
    <div class="col-md-4"><label class="form-label fw-semibold">Exam</label><select name="exam_id" class="form-select"><option value="">Select exam</option>@foreach ($exams as $exam)<option value="{{ $exam->id }}" @selected((string) ($filters['exam_id'] ?? '') === (string) $exam->id)>{{ $exam->exam_name }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label fw-semibold">Class</label><select name="class_id" class="form-select"><option value="">All</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected((string) ($filters['class_id'] ?? '') === (string) $class->id)>{{ $class->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label fw-semibold">Section</label><select name="section_id" class="form-select"><option value="">All</option>@foreach ($classes as $class)@foreach ($class->sections as $section)<option value="{{ $section->id }}" @selected((string) ($filters['section_id'] ?? '') === (string) $section->id)>{{ $section->name }}</option>@endforeach @endforeach</select></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Student</label><select name="student_id" class="form-select"><option value="">Select student</option>@foreach ($students as $item)<option value="{{ $item->id }}" @selected((string) ($filters['student_id'] ?? '') === (string) $item->id)>{{ $item->admission_number }} - {{ $item->full_name }}</option>@endforeach</select></div>
    <div class="col-md-1 d-flex align-items-end"><button class="btn btn-academic w-100">View</button></div>
</form>
@if ($student && $result)
<div class="text-center mb-4"><h3 class="text-navy mb-1">{{ $result->exam->exam_name ?? $exams->firstWhere('id', $result->exam_id)?->exam_name }}</h3><div class="text-muted">{{ $student->full_name }} | {{ $student->admission_number }}</div></div>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Subject</th><th>Marks</th><th>Maximum</th><th>Grade</th><th>Remarks</th></tr></thead><tbody>@foreach ($marks as $mark)<tr><td>{{ $mark->subject->name }}</td><td>{{ number_format($mark->marks_obtained, 2) }}</td><td>{{ number_format($mark->maximum_marks, 2) }}</td><td>{{ $mark->grade }}</td><td>{{ $mark->remarks ?: '-' }}</td></tr>@endforeach</tbody></table></div>
<div class="row g-3 mt-2"><div class="col-md"><div class="role-card"><div class="detail-label">Total</div><p class="detail-value">{{ number_format($result->total_marks, 2) }} / {{ number_format($result->maximum_total, 2) }}</p></div></div><div class="col-md"><div class="role-card"><div class="detail-label">Average</div><p class="detail-value">{{ number_format($result->average_marks, 2) }}%</p></div></div><div class="col-md"><div class="role-card"><div class="detail-label">Final Grade</div><p class="detail-value">{{ $result->final_grade }}</p></div></div><div class="col-md"><div class="role-card"><div class="detail-label">Result</div><p class="detail-value text-uppercase">{{ $result->result_status }}</p></div></div></div>
@elseif (! empty($filters['student_id']))
<div class="alert alert-info">No saved result is available for this selection.</div>
@endif
@endsection
