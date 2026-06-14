@extends('layouts.super_admin', ['pageTitle' => 'Class Result Report'])

@section('page_styles')
<style>@media print { .sidebar, .topbar, .report-filters, .print-button { display:none !important; } .main-content { margin:0; padding:0; } .content-area { box-shadow:none; } }</style>
@endsection

@section('super_admin_content')
<div class="page-heading"><div><h1>Class Result Report</h1><p>Compare totals, averages, grades, ranks, and result status.</p></div>@if ($results->isNotEmpty())<button onclick="window.print()" class="btn btn-outline-primary print-button"><i class="bi bi-printer me-2"></i>Print</button>@endif</div>
<form method="GET" class="row g-2 mb-4 report-filters">
    <div class="col-lg-3"><select name="exam_id" class="form-select"><option value="">Select exam</option>@foreach ($exams as $exam)<option value="{{ $exam->id }}" @selected((string) ($filters['exam_id'] ?? '') === (string) $exam->id)>{{ $exam->exam_name }}</option>@endforeach</select></div>
    <div class="col-lg-2"><select name="class_id" class="form-select"><option value="">All classes</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected((string) ($filters['class_id'] ?? '') === (string) $class->id)>{{ $class->name }}</option>@endforeach</select></div>
    <div class="col-lg-2"><select name="section_id" class="form-select"><option value="">All sections</option>@foreach ($classes as $class)@foreach ($class->sections as $section)<option value="{{ $section->id }}" @selected((string) ($filters['section_id'] ?? '') === (string) $section->id)>{{ $section->name }}</option>@endforeach @endforeach</select></div>
    <div class="col-lg-2"><select name="academic_year" class="form-select"><option value="">All years</option>@foreach ($academicYears as $year)<option value="{{ $year }}" @selected(($filters['academic_year'] ?? '') === $year)>{{ $year }}</option>@endforeach</select></div>
    <div class="col-lg-1"><select name="sort" class="form-select"><option value="rank">Rank</option><option value="total" @selected(($filters['sort'] ?? '') === 'total')>Total</option></select></div>
    <div class="col-lg-2"><button class="btn btn-academic w-100">Generate</button></div>
</form>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Rank</th><th>Student</th><th>Admission No.</th><th>Total Marks</th><th>Average</th><th>Grade</th><th>Result</th><th>Publish Status</th></tr></thead><tbody>@forelse ($results as $result)<tr><td><span class="role-badge">#{{ $result->rank }}</span></td><td class="fw-semibold">{{ $result->student->full_name }}</td><td>{{ $result->student->admission_number }}</td><td>{{ number_format($result->total_marks, 2) }} / {{ number_format($result->maximum_total, 2) }}</td><td>{{ number_format($result->average_marks, 2) }}%</td><td>{{ $result->final_grade }}</td><td class="text-uppercase">{{ $result->result_status }}</td><td><span class="status-badge status-{{ $result->status }}">{{ ucfirst($result->status) }}</span></td></tr>@empty<tr><td colspan="8"><div class="empty-state"><i class="bi bi-trophy"></i><h5>No class results found</h5></div></td></tr>@endforelse</tbody></table></div>
@endsection
