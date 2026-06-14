@extends('layouts.super_admin', ['pageTitle' => 'Edit Exam'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Edit Exam</h1><p>Update {{ $exam->exam_name }}.</p></div></div>
<form method="POST" action="{{ route('examinations.update', $exam) }}">
    @csrf
    @method('PUT')
    @include('examinations._form', ['submitLabel' => 'Update Exam'])
</form>
@endsection

@section('scripts')
    @parent
    @stack('exam_scripts')
@endsection
