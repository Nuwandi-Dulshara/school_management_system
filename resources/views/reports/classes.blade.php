@extends('layouts.super_admin', ['pageTitle' => 'Class Report'])
@section('super_admin_content')
@include('reports._styles')
<div class="page-heading"><div><h1>Class Report</h1><p>Class strength, subjects, teachers, attendance, and exams.</p></div>@include('reports._toolbar')</div>
<div class="card report-card mb-4 report-filters"><div class="card-body"><form class="row g-3">@include('reports._common_filters', ['fields' => ['academic_year','class_id','section_id']])<div class="col-12 d-flex gap-2"><button class="btn btn-academic">Apply Filters</button><a href="{{ route('reports.classes') }}" class="btn btn-outline-secondary">Reset</a></div></form></div></div>
<div class="card report-card"><div class="card-body table-responsive"><table class="table table-hover report-table"><thead><tr><th>Class</th><th>Section</th><th>Students</th><th>Subjects</th><th>Teachers</th><th>Attendance</th><th>Exams</th></tr></thead><tbody>@forelse($classRows as $row)<tr><td class="fw-semibold">{{ $row->class }}</td><td>{{ $row->section }}</td><td>{{ $row->students }}</td><td>{{ $row->subjects ?: '-' }}</td><td>{{ $row->teachers ?: '-' }}</td><td>{{ number_format($row->attendance,1) }}%</td><td>{{ $row->exams }}</td></tr>@empty<tr><td colspan="7" class="text-center py-5 text-muted">No class records found.</td></tr>@endforelse</tbody></table></div></div>
@endsection
