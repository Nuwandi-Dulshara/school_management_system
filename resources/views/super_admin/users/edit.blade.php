@extends('layouts.super_admin', ['pageTitle' => 'Edit User'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Edit User</h1>
        <p>Update {{ $managedUser->first_name }} {{ $managedUser->last_name }}'s account details and access.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.users.update', $managedUser) }}">
    @csrf
    @method('PUT')
    @include('super_admin.users._form', ['submitLabel' => 'Save Changes'])
</form>
@endsection
