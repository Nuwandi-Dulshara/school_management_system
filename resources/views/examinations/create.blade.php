@extends('layouts.super_admin', ['pageTitle' => 'Add Exam'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Add Exam</h1><p>Create a class and section examination.</p></div></div>
<form method="POST" action="{{ route('examinations.store') }}">
    @csrf
    @include('examinations._form', ['submitLabel' => 'Save Exam'])
</form>
@endsection

@section('scripts')
    @parent
    @stack('exam_scripts')
@endsection
