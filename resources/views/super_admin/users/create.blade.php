@extends('layouts.super_admin', ['pageTitle' => 'Add User'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Add User</h1>
        <p>Create a system user and assign an access role.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.users.store') }}">
    @csrf
    @include('super_admin.users._form', ['submitLabel' => 'Create User'])
</form>
@endsection
