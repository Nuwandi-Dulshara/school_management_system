@extends('layouts.super_admin', ['pageTitle' => 'Edit Teacher'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Edit Teacher</h1>
        <p>Update {{ $teacher->full_name }}'s profile and professional details.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.teachers.update', $teacher) }}">
    @csrf
    @method('PUT')
    @include('super_admin.teachers._form', ['submitLabel' => 'Save Changes'])
</form>
@endsection
