@extends('layouts.super_admin', ['pageTitle' => 'Add Notice'])
@section('super_admin_content')
<div class="page-heading"><div><h1>Add Notice</h1><p>Create a school announcement for a selected audience.</p></div></div>
<form method="POST" action="{{ route('notices.store') }}">@csrf @include('notices._form', ['submitLabel' => 'Save Notice'])</form>
@endsection
@section('scripts')@parent @stack('notice_scripts')@endsection
