@extends('layouts.super_admin', ['pageTitle' => 'Subject Management'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Subject List</h1>
        <p>Search, review, and manage subjects offered by the school.</p>
    </div>
    <a href="{{ route('super_admin.subjects.create') }}" class="btn btn-academic"><i class="bi bi-plus-lg me-2"></i>Add Subject</a>
</div>

@if ($manage === 'edit')
    <div class="alert alert-info"><i class="bi bi-info-circle-fill me-2"></i>Select the Edit action for the subject you want to update.</div>
@endif

<form method="GET" action="{{ route('super_admin.subjects.index') }}" class="row g-2 mb-4">
    @if ($manage)<input type="hidden" name="manage" value="{{ $manage }}">@endif
    <div class="col-md-7">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Subject name, code, or description">
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
            <a href="{{ route('super_admin.subjects.index', $manage ? ['manage' => $manage] : []) }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>ID</th><th>Code</th><th>Subject Name</th><th>Description</th><th>Classes</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($subjects as $subject)
                <tr>
                    <td class="text-muted">#{{ $subject->id }}</td>
                    <td><span class="role-badge">{{ $subject->code }}</span></td>
                    <td class="fw-semibold">{{ $subject->name }}</td>
                    <td>{{ $subject->description ? Str::limit($subject->description, 55) : 'No description' }}</td>
                    <td>{{ $subject->school_classes_count }}</td>
                    <td><span class="status-badge status-{{ $subject->status }}">{{ $statuses[$subject->status] }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end flex-wrap gap-1">
                            <a href="{{ route('super_admin.subjects.show', $subject) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('super_admin.subjects.edit', $subject) }}"
                                class="btn btn-sm {{ $manage === 'edit' ? 'btn-primary' : 'btn-outline-secondary' }}" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('super_admin.subjects.status', $subject) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm {{ $subject->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    title="{{ $subject->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $subject->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super_admin.subjects.destroy', $subject) }}"
                                onsubmit="return confirm('Delete this subject and remove its class assignments?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-journal-bookmark"></i><h5>No subjects found</h5><p class="mb-0">Create the first subject or adjust the filters.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($subjects->hasPages())<div class="mt-4">{{ $subjects->links() }}</div>@endif
@endsection
