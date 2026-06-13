@extends('layouts.super_admin', ['pageTitle' => 'Role Management'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Role Management</h1>
        <p>View the available system roles and their access responsibilities.</p>
    </div>
</div>

<div class="row g-4">
    @foreach ($roles as $role)
        <div class="col-md-6">
            <article class="role-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="role-icon"><i class="bi {{ $role['icon'] }}"></i></div>
                    <div>
                        <h5 class="text-navy fw-bold mb-1">{{ $role['name'] }}</h5>
                        <div class="text-muted small mb-3">{{ $role['key'] }}</div>
                        <p class="mb-0 text-secondary">{{ $role['description'] }}</p>
                    </div>
                </div>
            </article>
        </div>
    @endforeach
</div>
@endsection
