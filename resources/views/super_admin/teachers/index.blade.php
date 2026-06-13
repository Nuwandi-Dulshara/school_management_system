@extends('layouts.super_admin', ['pageTitle' => 'Teacher Management'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Teacher List</h1>
        <p>Search, review, and manage teacher profiles.</p>
    </div>
    <a href="{{ route('super_admin.teachers.create') }}" class="btn btn-academic">
        <i class="bi bi-person-plus-fill me-2"></i>Add Teacher
    </a>
</div>

@if ($manage)
    <div class="alert alert-info">
        <i class="bi bi-info-circle-fill me-2"></i>
        Select the {{ $manage === 'classes' ? 'Assigned Classes' : 'Assigned Subjects' }} action for the teacher you want to manage.
    </div>
@endif

<form method="GET" action="{{ route('super_admin.teachers.index') }}" class="row g-2 mb-4">
    @if ($manage)
        <input type="hidden" name="manage" value="{{ $manage }}">
    @endif
    <div class="col-md-7">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                placeholder="Name, employee number, email, phone, or subject">
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
        <button class="btn btn-academic flex-grow-1" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('super_admin.teachers.index', $manage ? ['manage' => $manage] : []) }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Teacher ID</th>
                <th>Full Name</th>
                <th>Employee No.</th>
                <th>Email</th>
                <th>Contact Number</th>
                <th>Main Subject</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($teachers as $teacher)
                <tr>
                    <td class="text-muted">#{{ $teacher->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar">{{ strtoupper(substr($teacher->full_name, 0, 2)) }}</span>
                            <span class="fw-semibold">{{ $teacher->full_name }}</span>
                        </div>
                    </td>
                    <td>{{ $teacher->employee_number }}</td>
                    <td>{{ $teacher->email }}</td>
                    <td>{{ $teacher->contact_number }}</td>
                    <td><span class="role-badge">{{ $teacher->main_subject }}</span></td>
                    <td><span class="status-badge status-{{ $teacher->status }}">{{ $statuses[$teacher->status] }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end flex-wrap gap-1">
                            <a href="{{ route('super_admin.teachers.show', $teacher) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('super_admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('super_admin.teachers.classes', $teacher) }}"
                                class="btn btn-sm {{ $manage === 'classes' ? 'btn-primary' : 'btn-outline-info' }}" title="Assigned Classes">
                                <i class="bi bi-building"></i>
                            </a>
                            <a href="{{ route('super_admin.teachers.subjects', $teacher) }}"
                                class="btn btn-sm {{ $manage === 'subjects' ? 'btn-primary' : 'btn-outline-dark' }}" title="Assigned Subjects">
                                <i class="bi bi-book"></i>
                            </a>
                            <form method="POST" action="{{ route('super_admin.teachers.status', $teacher) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm {{ $teacher->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    title="{{ $teacher->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $teacher->status === 'active' ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super_admin.teachers.destroy', $teacher) }}"
                                onsubmit="return confirm('Delete this teacher and all assignments permanently?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="bi bi-person-video3"></i>
                            <h5>No teachers found</h5>
                            <p class="mb-0">Try changing the search filter or add a teacher.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($teachers->hasPages())
    <div class="mt-4">{{ $teachers->links() }}</div>
@endif
@endsection
