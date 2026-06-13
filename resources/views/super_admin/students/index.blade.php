@extends('layouts.super_admin', ['pageTitle' => 'Student Management'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Student List</h1>
        <p>Search, filter, and manage student records.</p>
    </div>
    <a href="{{ route('super_admin.students.create') }}" class="btn btn-academic">
        <i class="bi bi-person-plus-fill me-2"></i>Add Student
    </a>
</div>

<form method="GET" action="{{ route('super_admin.students.index') }}" class="row g-2 mb-4">
    <div class="col-lg-4">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                placeholder="Name, admission number, email, or phone">
        </div>
    </div>
    <div class="col-sm-4 col-lg-2">
        <select name="class" class="form-select">
            <option value="">All classes</option>
            @foreach ($classes as $class)
                <option value="{{ $class }}" @selected(request('class') === $class)>Class {{ $class }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-4 col-lg-2">
        <select name="section" class="form-select">
            <option value="">All sections</option>
            @foreach ($sections as $section)
                <option value="{{ $section }}" @selected(request('section') === $section)>Section {{ $section }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-4 col-lg-2">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-2 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'class', 'section', 'status']))
            <a href="{{ route('super_admin.students.index') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Admission No.</th>
                <th>Class</th>
                <th>Section</th>
                <th>Parent / Guardian</th>
                <th>Contact Number</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td class="text-muted">#{{ $student->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar">{{ strtoupper(substr($student->full_name, 0, 2)) }}</span>
                            <span class="fw-semibold">{{ $student->full_name }}</span>
                        </div>
                    </td>
                    <td>{{ $student->admission_number }}</td>
                    <td>{{ $student->class }}</td>
                    <td>{{ $student->section }}</td>
                    <td>{{ $student->guardian?->guardian_name ?? 'Not provided' }}</td>
                    <td>{{ $student->contact_number }}</td>
                    <td><span class="status-badge status-{{ $student->status }}">{{ $statuses[$student->status] }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end flex-wrap gap-1">
                            <a href="{{ route('super_admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('super_admin.students.edit', $student) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('super_admin.students.guardians', $student) }}" class="btn btn-sm btn-outline-info" title="Parent or Guardian">
                                <i class="bi bi-people"></i>
                            </a>
                            <a href="{{ route('super_admin.students.documents', $student) }}" class="btn btn-sm btn-outline-dark" title="Documents">
                                <i class="bi bi-file-earmark-text"></i>
                            </a>
                            <form method="POST" action="{{ route('super_admin.students.status.update', $student) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $student->status === 'active' ? 'inactive' : 'active' }}">
                                <button class="btn btn-sm {{ $student->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    title="{{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $student->status === 'active' ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super_admin.students.destroy', $student) }}"
                                onsubmit="return confirm('Delete this student and related records permanently?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="bi bi-mortarboard"></i>
                            <h5>No students found</h5>
                            <p class="mb-0">Try changing the search or filters, or register a new student.</p>
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
