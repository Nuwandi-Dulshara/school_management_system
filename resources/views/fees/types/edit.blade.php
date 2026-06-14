@extends('layouts.super_admin', ['pageTitle' => 'Edit Fee Type'])
@section('super_admin_content')
<div class="page-heading"><div><h1>Edit Fee Type</h1><p>Update {{ $feeType->name }}.</p></div></div>
<form method="POST" action="{{ route('fees.types.update', $feeType) }}">@csrf @method('PUT') @include('fees.types._form', ['submitLabel' => 'Update Fee Type'])</form>
@endsection
