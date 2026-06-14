@extends('layouts.super_admin', ['pageTitle' => 'Edit Section'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Edit Section</h1>
        <p>Update section class, name, capacity, or status.</p>
    </div>
</div>

<form method="POST" action="{{ route('super_admin.sections.update', $schoolSection) }}">
    @csrf
    @method('PUT')
    @include('super_admin.sections._form', ['submitLabel' => 'Save Changes'])
</form>
@endsection
