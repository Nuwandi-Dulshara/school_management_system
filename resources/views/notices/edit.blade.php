@extends('layouts.super_admin', ['pageTitle' => 'Edit Notice'])
@section('super_admin_content')
<div class="page-heading"><div><h1>Edit Notice</h1><p>Update {{ $notice->title }}.</p></div></div>
<form method="POST" action="{{ route('notices.update', $notice) }}">@csrf @method('PUT') @include('notices._form', ['submitLabel' => 'Update Notice'])</form>
@endsection
@section('scripts')@parent @stack('notice_scripts')@endsection
