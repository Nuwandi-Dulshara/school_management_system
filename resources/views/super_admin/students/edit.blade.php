@extends('layouts.super_admin', ['pageTitle' => 'Edit Student'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Edit Student</h1>
        <p>Update {{ $student->full_name }}'s student and guardian information.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.students.update', $student) }}">
    @csrf
    @method('PUT')
    @include('super_admin.students._form', ['submitLabel' => 'Save Changes'])
</form>
@endsection
