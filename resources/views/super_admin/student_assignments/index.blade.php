@extends('layouts.super_admin', ['pageTitle' => 'Class-wise Student List'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Class-wise Student List</h1>
        <p>Search and filter student assignments by academic year, class, section, and status.</p>
    </div>
    <a href="{{ route('super_admin.student_assignments.create') }}" class="btn btn-academic"><i class="bi bi-plus-lg me-2"></i>Assign Student</a>
</div>

<form method="GET" action="{{ route('super_admin.student_assignments.index') }}" class="row g-2 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, admission no. or ID"></div>
    </div>
    <div class="col-xl-2 col-md-3">
        <select name="academic_year" class="form-select"><option value="">All academic years</option>@foreach ($academicYears as $year)<option value="{{ $year }}" @selected(request('academic_year') === $year)>{{ $year }}</option>@endforeach</select>
    </div>
    <div class="col-xl-2 col-md-3">
        <select name="class_id" id="filter_class_id" class="form-select"><option value="">All classes</option>@foreach ($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected((string) request('class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach</select>
    </div>
    <div class="col-xl-2 col-md-4">
        <select name="section_id" id="filter_section_id" class="form-select"><option value="">All sections</option>@foreach ($classes as $schoolClass)@foreach ($schoolClass->sections as $section)<option value="{{ $section->id }}" data-class-id="{{ $schoolClass->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $schoolClass->name }} - {{ $section->name }}</option>@endforeach @endforeach</select>
    </div>
    <div class="col-xl-1 col-md-4">
        <select name="status" class="form-select"><option value="">Status</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
    </div>
    <div class="col-xl-2 col-md-4 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1">Filter</button>
        @if (request()->hasAny(['search', 'academic_year', 'class_id', 'section_id', 'status']))<a href="{{ route('super_admin.student_assignments.index') }}" class="btn btn-outline-secondary">Clear</a>@endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Student ID</th><th>Student</th><th>Academic Year</th><th>Class</th><th>Section</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($assignments as $assignment)
                <tr>
                    <td>#{{ $assignment->student_id }}</td>
                    <td><div class="fw-semibold text-navy">{{ $assignment->student->full_name }}</div><small class="text-muted">{{ $assignment->student->admission_number }}</small></td>
                    <td>{{ $assignment->academic_year }}</td>
                    <td>{{ $assignment->schoolClass->name }}</td>
                    <td>{{ $assignment->schoolSection->name }}</td>
                    <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('super_admin.student_assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                            @if ($assignment->status === 'assigned')
                                <a href="{{ route('super_admin.student_assignments.transfers', ['assignment' => $assignment->id]) }}" class="btn btn-sm btn-outline-warning" title="Transfer"><i class="bi bi-arrow-left-right"></i></a>
                                <a href="{{ route('super_admin.student_assignments.create', ['edit' => $assignment->id]) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('super_admin.student_assignments.destroy', $assignment) }}" onsubmit="return confirm('Remove this student assignment?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-x-lg"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-person-check"></i><h5>No assignments found</h5><p class="mb-0">Adjust the filters or assign a student.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($assignments->hasPages())<div class="mt-4">{{ $assignments->links() }}</div>@endif
@endsection

@section('scripts')
@parent
<script>
    const filterClass = document.getElementById('filter_class_id');
    const filterSection = document.getElementById('filter_section_id');

    function filterListSections() {
        const selectedClass = filterClass?.value;
        if (!filterSection) return;
        Array.from(filterSection.options).forEach((option) => {
            if (!option.value) return;
            const visible = !selectedClass || option.dataset.classId === selectedClass;
            option.hidden = !visible;
            option.disabled = !visible;
        });
        if (filterSection.selectedOptions[0]?.disabled) filterSection.value = '';
    }

    filterClass?.addEventListener('change', filterListSections);
    filterListSections();
</script>
@endsection
