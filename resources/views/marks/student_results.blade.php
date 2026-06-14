@extends('layouts.super_admin', ['pageTitle' => 'My Results'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Student Result View</h1><p>Only your published examination results are shown here.</p></div></div>
@if (! $student)<div class="alert alert-warning">Your login email is not linked to a student record.</div>@endif
@forelse ($results as $result)
<div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white py-3 d-flex justify-content-between"><div><h5 class="text-navy mb-1">{{ $result->exam->exam_name }}</h5><small class="text-muted">{{ $result->academic_year }}</small></div><span class="status-badge status-published">Published</span></div><div class="card-body">
    <div class="table-responsive"><table class="table"><thead><tr><th>Subject</th><th>Marks</th><th>Maximum</th><th>Grade</th><th>Remarks</th></tr></thead><tbody>@foreach ($result->publishedMarks as $mark)<tr><td>{{ $mark->subject->name }}</td><td>{{ number_format($mark->marks_obtained, 2) }}</td><td>{{ number_format($mark->maximum_marks, 2) }}</td><td>{{ $mark->grade }}</td><td>{{ $mark->remarks ?: '-' }}</td></tr>@endforeach</tbody></table></div>
    <div class="row g-3"><div class="col-md-3"><strong>Total:</strong> {{ number_format($result->total_marks, 2) }} / {{ number_format($result->maximum_total, 2) }}</div><div class="col-md-3"><strong>Average:</strong> {{ number_format($result->average_marks, 2) }}%</div><div class="col-md-3"><strong>Grade:</strong> {{ $result->final_grade }}</div><div class="col-md-3"><strong>Result:</strong> <span class="text-uppercase">{{ $result->result_status }}</span></div></div>
</div></div>
@empty
@if ($student)<div class="empty-state"><i class="bi bi-file-earmark-bar-graph"></i><h5>No published results</h5><p class="mb-0">Published results will appear here.</p></div>@endif
@endforelse
@endsection
