@extends('layouts.super_admin', ['pageTitle' => 'Edit Marks'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Edit Marks</h1><p>{{ $mark->student->full_name }} - {{ $mark->subject->name }}</p></div></div>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="detail-label">Exam</div><p class="detail-value">{{ $mark->exam->exam_name }}</p></div>
    <div class="col-md-3"><div class="detail-label">Class / Section</div><p class="detail-value">{{ $mark->schoolClass->name }} / {{ $mark->schoolSection->name }}</p></div>
    <div class="col-md-3"><div class="detail-label">Student</div><p class="detail-value">{{ $mark->student->full_name }}</p></div>
    <div class="col-md-3"><div class="detail-label">Subject</div><p class="detail-value">{{ $mark->subject->name }}</p></div>
</div>
<form method="POST" action="{{ route('marks.update', $mark) }}" class="row g-3">
    @csrf @method('PUT')
    <div class="col-md-3"><label class="form-label fw-semibold">Marks Obtained</label><input type="number" step="0.01" min="0" name="marks_obtained" id="marks_obtained" class="form-control" value="{{ old('marks_obtained', $mark->marks_obtained) }}" required></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Maximum Marks</label><input type="number" step="0.01" min="0.01" name="maximum_marks" id="maximum_marks" class="form-control" value="{{ old('maximum_marks', $mark->maximum_marks) }}" required></div>
    <div class="col-md-2"><label class="form-label fw-semibold">Grade</label><input type="text" id="grade_preview" class="form-control" value="{{ $mark->grade }}" readonly></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select">@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $mark->status) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label fw-semibold">Remarks</label><textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $mark->remarks) }}</textarea></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-academic">Update Mark</button><a href="{{ route('marks.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form>
@endsection

@section('scripts')
@parent
<script>
    const obtained = document.getElementById('marks_obtained');
    const maximum = document.getElementById('maximum_marks');
    const preview = document.getElementById('grade_preview');
    const updateGrade = () => {
        const percentage = Number(maximum.value) > 0 ? Number(obtained.value) / Number(maximum.value) * 100 : 0;
        preview.value = percentage >= 75 ? 'A' : percentage >= 65 ? 'B' : percentage >= 50 ? 'C' : percentage >= 35 ? 'S' : 'F';
    };
    obtained.addEventListener('input', updateGrade);
    maximum.addEventListener('input', updateGrade);
</script>
@endsection
