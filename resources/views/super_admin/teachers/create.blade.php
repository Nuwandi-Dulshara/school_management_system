@extends('layouts.super_admin', ['pageTitle' => 'Add Teacher'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Add Teacher</h1>
        <p>Create a teacher profile and record their professional details.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.teachers.store') }}">
    @csrf
    @include('super_admin.teachers._form', ['submitLabel' => 'Add Teacher'])
</form>
@endsection
