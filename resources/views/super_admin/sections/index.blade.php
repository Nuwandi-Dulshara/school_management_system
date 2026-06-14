@extends('layouts.super_admin', ['pageTitle' => 'Section List'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Section List</h1>
        <p>Search and manage sections assigned to school classes.</p>
    </div>
    <a href="{{ route('super_admin.sections.create') }}" class="btn btn-academic"><i class="bi bi-plus-lg me-2"></i>Add Section</a>
</div>

<form method="GET" action="{{ route('super_admin.sections.index') }}" class="row g-2 mb-4">
    <div class="col-lg-4">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Section or class name">
        </div>
    </div>
    <div class="col-sm-4 col-lg-3">
        <select name="class_id" class="form-select">
            <option value="">All classes</option>
            @foreach ($classes as $schoolClass)
                <option value="{{ $schoolClass->id }}" @selected((string) request('class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-4 col-lg-3">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-4 col-lg-2 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1">Filter</button>
        @if (request()->hasAny(['search', 'class_id', 'status']))<a href="{{ route('super_admin.sections.index') }}" class="btn btn-outline-secondary">Clear</a>@endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>ID</th><th>Class</th><th>Section</th><th>Capacity</th><th>Students</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($sections as $section)
                <tr>
                    <td class="text-muted">#{{ $section->id }}</td>
                    <td><a href="{{ route('super_admin.classes.show', $section->schoolClass) }}" class="fw-semibold text-navy text-decoration-none">{{ $section->schoolClass->name }}</a></td>
                    <td>{{ $section->name }}</td>
                    <td>{{ $section->capacity }}</td>
                    <td>{{ $section->students_count }} / {{ $section->capacity }}</td>
                    <td><span class="status-badge status-{{ $section->status }}">{{ $statuses[$section->status] }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('super_admin.sections.edit', $section) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('super_admin.sections.status', $section) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm {{ $section->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    title="{{ $section->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $section->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super_admin.sections.destroy', $section) }}"
                                onsubmit="return confirm('Delete this section?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-grid-3x3-gap"></i><h5>No sections found</h5><p class="mb-0">Create a section or adjust the filters.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($sections->hasPages())<div class="mt-4">{{ $sections->links() }}</div>@endif
@endsection
