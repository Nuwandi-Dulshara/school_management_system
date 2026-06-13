@extends('layouts.super_admin', ['pageTitle' => 'Add Student'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Add Student</h1>
        <p>Register a student together with their parent or guardian details.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.students.store') }}">
    @csrf
    @include('super_admin.students._form', ['submitLabel' => 'Register Student'])
</form>
@endsection
