@extends('layouts.super_admin', ['pageTitle' => 'Class Exam List'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Class Exam List</h1><p>View examinations assigned to each class and section.</p></div></div>
<form method="GET" class="row g-2 mb-4">
    <div class="col-md-3"><select name="class_id" id="filter_class_id" class="form-select"><option value="">All classes</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>{{ $class->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="section_id" id="filter_section_id" class="form-select"><option value="">All sections</option>@foreach ($classes as $class)@foreach ($class->sections as $section)<option value="{{ $section->id }}" data-class-id="{{ $class->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $class->name }} - {{ $section->name }}</option>@endforeach @endforeach</select></div>
    <div class="col-md-3"><select name="academic_year" class="form-select"><option value="">All academic years</option>@foreach ($academicYears as $year)<option value="{{ $year }}" @selected(request('academic_year') === $year)>{{ $year }}</option>@endforeach</select></div>
    <div class="col-md-3 d-flex gap-2"><button class="btn btn-academic flex-grow-1">Filter</button><a href="{{ route('examinations.class_exams') }}" class="btn btn-outline-secondary">Clear</a></div>
</form>
<div class="table-responsive"><table class="table table-hover">
    <thead><tr><th>Class</th><th>Section</th><th>Exam Name</th><th>Exam Type</th><th>Academic Year</th><th>Schedule Status</th><th>Publish Status</th></tr></thead>
    <tbody>@forelse ($exams as $exam)<tr><td>{{ $exam->schoolClass->name }}</td><td>{{ $exam->schoolSection->name }}</td><td class="fw-semibold">{{ $exam->exam_name }}</td><td>{{ $exam->examType->name }}</td><td>{{ $exam->academic_year }}</td><td><a href="{{ route('examinations.schedules.index', ['exam_id' => $exam->id]) }}" class="text-decoration-none">{{ $exam->schedules_count ? $exam->schedules_count.' subject(s)' : 'Not scheduled' }}</a></td><td><span class="status-badge status-{{ $exam->status }}">{{ $statuses[$exam->status] }}</span></td></tr>@empty<tr><td colspan="7"><div class="empty-state"><i class="bi bi-calendar-x"></i><h5>No class exams found</h5></div></td></tr>@endforelse</tbody>
</table></div>
@if ($exams->hasPages())<div class="mt-4">{{ $exams->links() }}</div>@endif
@endsection

@section('scripts')
@parent
<script>
    const filterClass = document.getElementById('filter_class_id');
    const filterSection = document.getElementById('filter_section_id');
    const updateSections = () => [...filterSection.options].forEach((option, index) => option.hidden = index > 0 && filterClass.value && option.dataset.classId !== filterClass.value);
    filterClass.addEventListener('change', () => { filterSection.value = ''; updateSections(); });
    updateSections();
</script>
@endsection
