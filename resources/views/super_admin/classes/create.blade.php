@extends('layouts.super_admin', ['pageTitle' => 'Add Class'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Add Class</h1>
        <p>Create a class and define its total student capacity.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.classes.store') }}">
    @csrf
    @include('super_admin.classes._form', ['submitLabel' => 'Create Class'])
</form>
@endsection
