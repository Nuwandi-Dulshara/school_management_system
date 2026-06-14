@extends('layouts.super_admin', ['pageTitle' => 'Class & Section Management'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Class List</h1>
        <p>Manage classes, capacity, sections, and student assignments.</p>
    </div>
    <a href="{{ route('super_admin.classes.create') }}" class="btn btn-academic">
        <i class="bi bi-plus-lg me-2"></i>Add Class
    </a>
</div>

@if ($manage === 'students')
    <div class="alert alert-info">
        <i class="bi bi-info-circle-fill me-2"></i>Select the student-list action for the class you want to manage.
    </div>
@endif

<form method="GET" action="{{ route('super_admin.classes.index') }}" class="row g-2 mb-4">
    @if ($manage)<input type="hidden" name="manage" value="{{ $manage }}">@endif
    <div class="col-md-7">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search classes">
        </div>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1">Filter</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('super_admin.classes.index', $manage ? ['manage' => $manage] : []) }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Class Name</th>
                <th>Capacity</th>
                <th>Students</th>
                <th>Sections</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($classes as $schoolClass)
                <tr>
                    <td class="text-muted">#{{ $schoolClass->id }}</td>
                    <td class="fw-semibold">{{ $schoolClass->name }}</td>
                    <td>{{ $schoolClass->capacity }}</td>
                    <td>{{ $schoolClass->students_count }} / {{ $schoolClass->capacity }}</td>
                    <td>{{ $schoolClass->sections_count }}</td>
                    <td><span class="status-badge status-{{ $schoolClass->status }}">{{ $statuses[$schoolClass->status] }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end flex-wrap gap-1">
                            <a href="{{ route('super_admin.classes.show', $schoolClass) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('super_admin.classes.edit', $schoolClass) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('super_admin.classes.students', $schoolClass) }}"
                                class="btn btn-sm {{ $manage === 'students' ? 'btn-primary' : 'btn-outline-info' }}" title="Student List">
                                <i class="bi bi-people"></i>
                            </a>
                            <form method="POST" action="{{ route('super_admin.classes.status', $schoolClass) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm {{ $schoolClass->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    title="{{ $schoolClass->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $schoolClass->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super_admin.classes.destroy', $schoolClass) }}"
                                onsubmit="return confirm('Delete this class?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-diagram-3"></i><h5>No classes found</h5><p class="mb-0">Create the first class or adjust the filters.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($classes->hasPages())<div class="mt-4">{{ $classes->links() }}</div>@endif
@endsection
