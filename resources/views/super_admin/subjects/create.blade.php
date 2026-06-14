@extends('layouts.super_admin', ['pageTitle' => 'Add Subject'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Add Subject</h1>
        <p>Create a subject that can be assigned to managed classes.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.subjects.store') }}">
    @csrf
    @include('super_admin.subjects._form', ['submitLabel' => 'Create Subject'])
</form>
@endsection
