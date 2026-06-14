@extends('layouts.super_admin', ['pageTitle' => 'Add Fee Type'])
@section('super_admin_content')
<div class="page-heading"><div><h1>Add Fee Type</h1><p>Create a new school fee category.</p></div></div>
<form method="POST" action="{{ route('fees.types.store') }}">@csrf @include('fees.types._form', ['submitLabel' => 'Save Fee Type'])</form>
@endsection
