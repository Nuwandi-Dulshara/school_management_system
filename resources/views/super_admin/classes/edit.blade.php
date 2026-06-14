@extends('layouts.super_admin', ['pageTitle' => 'Edit Class'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Edit Class</h1>
        <p>Update {{ $schoolClass->name }} details, capacity, or status.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.classes.update', $schoolClass) }}">
    @csrf
    @method('PUT')
    @include('super_admin.classes._form', ['submitLabel' => 'Save Changes'])
</form>
@endsection
