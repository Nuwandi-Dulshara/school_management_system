@extends('layouts.super_admin', ['pageTitle' => 'Edit Subject'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Edit Subject</h1>
        <p>Update {{ $subject->name }} details or status.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.subjects.update', $subject) }}">
    @csrf
    @method('PUT')
    @include('super_admin.subjects._form', ['submitLabel' => 'Save Changes'])
</form>
@endsection
