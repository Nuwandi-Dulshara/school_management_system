@extends('layouts.super_admin', ['pageTitle' => 'Marks Entry'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Marks Entry</h1><p>Select an exam and subject, then enter marks for the class.</p></div></div>

<form method="GET" action="{{ route('marks.entry') }}" class="row g-3 mb-4">
    @include('marks._selection_filters')
    <div class="col-12"><button class="btn btn-academic"><i class="bi bi-people me-2"></i>Load Students</button></div>
</form>

@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@if ($students->isNotEmpty())
<form method="POST" action="{{ route('marks.store') }}">
    @csrf
    @foreach (['exam_id', 'class_id', 'section_id', 'subject_id'] as $field)<input type="hidden" name="{{ $field }}" value="{{ $filters[$field] }}">@endforeach
    <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
        <div><h5 class="text-navy mb-1">Student Marks</h5><small class="text-muted">Grades update automatically from the percentage score.</small></div>
        <div><label class="form-label fw-semibold">Result Status</label><select name="status" class="form-select">@foreach ($statuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
    </div>
    <div class="table-responsive"><table class="table table-hover">
        <thead><tr><th>Student</th><th>Admission No.</th><th>Marks Obtained</th><th>Maximum Marks</th><th>Grade</th><th>Remarks</th></tr></thead>
        <tbody>
        @foreach ($students as $index => $student)
            @php($saved = $existingMarks->get($student->id))
            <tr data-mark-row>
                <td class="fw-semibold">{{ $student->full_name }}<input type="hidden" name="marks[{{ $index }}][student_id]" value="{{ $student->id }}"></td>
                <td>{{ $student->admission_number }}</td>
                <td><input type="number" step="0.01" min="0" name="marks[{{ $index }}][marks_obtained]" class="form-control mark-obtained" value="{{ old("marks.$index.marks_obtained", $saved?->marks_obtained) }}" required></td>
                <td><input type="number" step="0.01" min="0.01" name="marks[{{ $index }}][maximum_marks]" class="form-control mark-maximum" value="{{ old("marks.$index.maximum_marks", $saved?->maximum_marks ?? 100) }}" required></td>
                <td><span class="role-badge mark-grade">{{ $saved?->grade ?? '-' }}</span></td>
                <td><input type="text" name="marks[{{ $index }}][remarks]" class="form-control" value="{{ old("marks.$index.remarks", $saved?->remarks) }}" placeholder="Optional"></td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>Save Marks</button>
</form>
@elseif ($filters)
    <div class="empty-state"><i class="bi bi-people"></i><h5>No eligible students found</h5><p class="mb-0">Check the selected class, section, and academic year.</p></div>
@endif
@endsection

@section('scripts')
@parent
@include('marks._selection_script')
<script>
    const gradeFor = (marks, maximum) => {
        const score = maximum > 0 ? (marks / maximum) * 100 : 0;
        if (score >= 75) return 'A';
        if (score >= 65) return 'B';
        if (score >= 50) return 'C';
        if (score >= 35) return 'S';
        return 'F';
    };
    document.querySelectorAll('[data-mark-row]').forEach((row) => {
        const obtained = row.querySelector('.mark-obtained');
        const maximum = row.querySelector('.mark-maximum');
        const grade = row.querySelector('.mark-grade');
        const update = () => grade.textContent = obtained.value === '' ? '-' : gradeFor(Number(obtained.value), Number(maximum.value));
        obtained.addEventListener('input', update);
        maximum.addEventListener('input', update);
        update();
    });
</script>
@endsection
