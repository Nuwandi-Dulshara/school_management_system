@extends('layouts.super_admin', ['pageTitle' => 'User Management'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>User List</h1>
        <p>Search, review, and manage system user accounts.</p>
    </div>
    <a href="{{ route('super_admin.users.create') }}" class="btn btn-academic">
        <i class="bi bi-person-plus-fill me-2"></i>Add User
    </a>
</div>

<form method="GET" action="{{ route('super_admin.users.index') }}" class="row g-2 mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                placeholder="Search by name, email, or username">
        </div>
    </div>
    <div class="col-md-3">
        <select name="role" class="form-select">
            <option value="">All roles</option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'role']))
            <a href="{{ route('super_admin.users.index') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td class="text-muted">#{{ $user->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar">{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                            <span class="fw-semibold">{{ $user->first_name }} {{ $user->last_name }}</span>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->username }}</td>
                    <td><span class="role-badge">{{ $roles[$user->role] ?? ucfirst($user->role) }}</span></td>
                    <td><span class="status-badge status-{{ $user->status }}">{{ ucfirst($user->status) }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end flex-wrap gap-1">
                            <a href="{{ route('super_admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('super_admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if (!Auth::user()->is($user))
                                <form method="POST" action="{{ route('super_admin.users.status', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                        title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi {{ $user->status === 'active' ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('super_admin.users.destroy', $user) }}"
                                    onsubmit="return confirm('Delete this user permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            <h5>No users found</h5>
                            <p class="mb-0">Try changing the search or role filter.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->hasPages())
    <div class="mt-4">{{ $users->links() }}</div>
@endif
@endsection
