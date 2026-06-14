@extends('layouts.super_admin', ['pageTitle' => 'Add Section'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Add Section</h1>
        <p>Create a section under an existing class and define its capacity.</p>
    </div>
</div>

@if ($classes->isEmpty())
    <div class="alert alert-warning">
        Create an active class before adding a section.
        <a href="{{ route('super_admin.classes.create') }}" class="alert-link">Add Class</a>
    </div>
@else
    <form method="POST" action="{{ route('super_admin.sections.store') }}">
        @csrf
        @include('super_admin.sections._form', ['submitLabel' => 'Create Section'])
    </form>
@endif
@endsection
