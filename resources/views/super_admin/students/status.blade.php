@extends('layouts.super_admin', ['pageTitle' => 'Student Status'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Student Status</h1>
        <p>Review status totals and update individual student records.</p>
    </div>
    <a href="{{ route('super_admin.students.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-people me-2"></i>Student List
    </a>
</div>

<div class="row g-3 mb-4">
    @foreach ($statuses as $value => $label)
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('super_admin.students.status', ['status' => $value]) }}" class="text-decoration-none">
                <div class="border rounded-3 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1">{{ $label }}</div>
                            <div class="fs-3 fw-bold text-navy">{{ $statusCounts[$value] ?? 0 }}</div>
                        </div>
                        <span class="status-badge status-{{ $value }}">{{ $label }}</span>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('super_admin.students.status') }}" class="row g-2 mb-4">
    <div class="col-md-4">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-academic" type="submit">Filter</button>
        @if (request('status'))
            <a href="{{ route('super_admin.students.status') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Admission Number</th>
                <th>Student</th>
                <th>Class</th>
                <th>Section</th>
                <th>Current Status</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student->admission_number }}</td>
                    <td>
                        <a href="{{ route('super_admin.students.show', $student) }}" class="fw-semibold text-navy text-decoration-none">
                            {{ $student->full_name }}
                        </a>
                    </td>
                    <td>{{ $student->class }}</td>
                    <td>{{ $student->section }}</td>
                    <td><span class="status-badge status-{{ $student->status }}">{{ $statuses[$student->status] }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('super_admin.students.status.update', $student) }}" class="d-flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select form-select-sm" aria-label="Status for {{ $student->full_name }}">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($student->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-academic" type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-person-check"></i>
                            <h5>No students found</h5>
                            <p class="mb-0">There are no students for the selected status.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($students->hasPages())
    <div class="mt-4">{{ $students->links() }}</div>
@endif
@endsection
