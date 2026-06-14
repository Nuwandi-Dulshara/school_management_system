@extends('layouts.super_admin', ['pageTitle' => 'Dashboard'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Super Admin Dashboard</h1>
        <p>Manage system users and their access levels from the User Management module.</p>
    </div>
    <a href="{{ route('super_admin.users.create') }}" class="btn btn-academic">
        <i class="bi bi-person-plus-fill me-2"></i>Add User
    </a>
</div>
@include('dashboards._notices')
@endsection
