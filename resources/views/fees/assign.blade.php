@extends('layouts.super_admin', ['pageTitle' => 'Assign Fees'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Assign Fees</h1><p>Assign a fee to one student or an entire class and section.</p></div></div>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if ($editAssignment)
<div class="alert alert-info">Editing {{ $editAssignment->feeType->name }} for {{ $editAssignment->student->full_name }}. Student, fee type, and academic year remain fixed.</div>
<form method="POST" action="{{ route('fees.assignments.update', $editAssignment) }}" class="row g-3">@csrf @method('PUT')
    <div class="col-md-4"><label class="form-label fw-semibold">Assigned Amount</label><input type="number" step="0.01" min="0.01" name="assigned_amount" class="form-control" value="{{ old('assigned_amount', $editAssignment->assigned_amount) }}" required></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control" value="{{ old('due_date', $editAssignment->due_date->toDateString()) }}" required></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select">@foreach ($assignmentStatuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $editAssignment->status) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-academic">Update Assignment</button><a href="{{ route('fees.assign') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form>
@else
<form method="POST" action="{{ route('fees.assignments.store') }}" class="row g-3">@csrf
    <div class="col-md-4"><label class="form-label fw-semibold">Academic Year</label><select name="academic_year" class="form-select" required><option value="">Select year</option>@foreach ($academicYears as $year)<option value="{{ $year }}" @selected(old('academic_year') === $year)>{{ $year }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Fee Type</label><select name="fee_type_id" id="fee_type_id" class="form-select" required><option value="">Select fee type</option>@foreach ($feeTypes as $type)<option value="{{ $type->id }}" data-amount="{{ $type->amount }}" @selected((string) old('fee_type_id') === (string) $type->id)>{{ $type->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Assigned Amount</label><input type="number" step="0.01" min="0.01" name="assigned_amount" id="assigned_amount" class="form-control" value="{{ old('assigned_amount') }}" required></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Class</label><select name="school_class_id" id="school_class_id" class="form-select" required><option value="">Select class</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected((string) old('school_class_id') === (string) $class->id)>{{ $class->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Section</label><select name="school_section_id" id="school_section_id" class="form-select" required><option value="">Select section</option>@foreach ($classes as $class)@foreach ($class->sections as $section)<option value="{{ $section->id }}" data-class-id="{{ $class->id }}" @selected((string) old('school_section_id') === (string) $section->id)>{{ $class->name }} - {{ $section->name }}</option>@endforeach @endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Student <span class="text-muted">(optional)</span></label><select name="student_id" id="student_id" class="form-select"><option value="">Entire class / section</option>@foreach ($students as $student)<option value="{{ $student->id }}" data-class-id="{{ $student->school_class_id }}" data-section-id="{{ $student->school_section_id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->admission_number }} - {{ $student->full_name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select">@foreach ($assignmentStatuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
    <div class="col-12"><button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>Assign Fee</button></div>
</form>
@endif
@endsection

@section('scripts')
@parent
@include('fees._class_section_script')
<script>
    const feeType = document.getElementById('fee_type_id');
    const amount = document.getElementById('assigned_amount');
    feeType?.addEventListener('change', () => { if (feeType.selectedOptions[0]?.dataset.amount) amount.value = feeType.selectedOptions[0].dataset.amount; });
</script>
@endsection
