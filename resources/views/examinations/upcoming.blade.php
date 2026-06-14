@extends('layouts.super_admin', ['pageTitle' => 'Upcoming Exams'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Upcoming Exams</h1><p>Future examinations sorted by the nearest start date.</p></div></div>
<div class="table-responsive"><table class="table table-hover">
    <thead><tr><th>Exam Name</th><th>Class</th><th>Section</th><th>Exam Type</th><th>Start Date</th><th>End Date</th><th>Status</th></tr></thead>
    <tbody>@forelse ($exams as $exam)<tr><td class="fw-semibold">{{ $exam->exam_name }}</td><td>{{ $exam->schoolClass->name }}</td><td>{{ $exam->schoolSection->name }}</td><td>{{ $exam->examType->name }}</td><td>{{ $exam->start_date->format('M d, Y') }}</td><td>{{ $exam->end_date->format('M d, Y') }}</td><td><span class="status-badge status-{{ $exam->status }}">{{ $statuses[$exam->status] }}</span></td></tr>@empty<tr><td colspan="7"><div class="empty-state"><i class="bi bi-calendar2-event"></i><h5>No upcoming exams</h5></div></td></tr>@endforelse</tbody>
</table></div>
@if ($exams->hasPages())<div class="mt-4">{{ $exams->links() }}</div>@endif
@endsection
