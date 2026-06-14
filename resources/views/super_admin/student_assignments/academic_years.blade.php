@extends('layouts.super_admin', ['pageTitle' => 'Academic Year Assignment'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Academic Year Assignment</h1>
        <p>Review assignment totals and student records for each academic year.</p>
    </div>
    <a href="{{ route('super_admin.student_assignments.create') }}" class="btn btn-academic"><i class="bi bi-plus-lg me-2"></i>Assign Student</a>
</div>

<div class="row g-3 mb-4">
    @forelse ($yearSummaries as $summary)
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('super_admin.student_assignments.academic_years', ['academic_year' => $summary->academic_year]) }}" class="text-decoration-none">
                <div class="role-card {{ $selectedYear === $summary->academic_year ? 'border-primary' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div><div class="detail-label">Academic Year</div><h5 class="text-navy fw-bold">{{ $summary->academic_year }}</h5></div>
                        <div class="role-icon"><i class="bi bi-calendar3"></i></div>
                    </div>
                    <div class="small text-muted mt-3">{{ $summary->assigned_count }} assigned · {{ $summary->transferred_count }} transferred · {{ $summary->removed_count }} removed</div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-light border">No academic year assignments have been recorded yet.</div></div>
    @endforelse
</div>

<form method="GET" action="{{ route('super_admin.student_assignments.academic_years') }}" class="row g-2 mb-4">
    <div class="col-md-5">
        <select name="academic_year" class="form-select">@foreach ($academicYears as $year)<option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>@endforeach</select>
    </div>
    <div class="col-md-2"><button class="btn btn-academic w-100">View Year</button></div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Student</th><th>Admission Number</th><th>Class</th><th>Section</th><th>Status</th><th>Assigned Date</th></tr></thead>
        <tbody>
            @forelse ($assignments as $assignment)
                <tr>
                    <td><a href="{{ route('super_admin.student_assignments.show', $assignment) }}" class="fw-semibold text-navy text-decoration-none">{{ $assignment->student->full_name }}</a></td>
                    <td>{{ $assignment->student->admission_number }}</td>
                    <td>{{ $assignment->schoolClass->name }}</td>
                    <td>{{ $assignment->schoolSection->name }}</td>
                    <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                    <td>{{ $assignment->assigned_at?->format('M j, Y') ?? $assignment->created_at->format('M j, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No assignments found for {{ $selectedYear }}.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($assignments->hasPages())<div class="mt-4">{{ $assignments->links() }}</div>@endif
@endsection
