@extends('layouts.super_admin', ['pageTitle' => 'Assign Students'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>{{ $editAssignment ? 'Edit Student Assignment' : 'Assign Students' }}</h1>
        <p>Select an academic year, class, section, and student to create an assignment.</p>
    </div>
    <a href="{{ route('super_admin.student_assignments.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list-ul me-2"></i>Assigned Student List
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@if ($classes->isEmpty() || $students->isEmpty())
    <div class="alert alert-warning">At least one student, class, and section are required before creating an assignment.</div>
@else
    <div class="border rounded-3 p-4 mb-4">
        <h5 class="text-navy fw-bold mb-3">{{ $editAssignment ? 'Update Assignment' : 'New Assignment' }}</h5>
        <form method="POST" action="{{ $editAssignment
            ? route('super_admin.student_assignments.update', $editAssignment)
            : route('super_admin.student_assignments.store') }}">
            @csrf
            @if ($editAssignment) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label for="academic_year" class="form-label fw-semibold">Academic Year</label>
                    <select name="academic_year" id="academic_year" class="form-select" required>
                        @foreach ($academicYears as $academicYear)
                            <option value="{{ $academicYear }}" @selected(old('academic_year', $editAssignment?->academic_year ?? \App\Http\Controllers\SuperAdmin\StudentClassAssignmentController::currentAcademicYear()) === $academicYear)>
                                {{ $academicYear }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6">
                    <label for="student_id" class="form-label fw-semibold">Student</label>
                    <select name="{{ $editAssignment ? '' : 'student_id' }}" id="student_id" class="form-select" required @disabled($editAssignment)>
                        <option value="">Select a student</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected((string) old('student_id', $editAssignment?->student_id) === (string) $student->id)>
                                #{{ $student->id }} - {{ $student->admission_number }} - {{ $student->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($editAssignment)
                        <input type="hidden" name="student_id" value="{{ $editAssignment->student_id }}">
                        <div class="form-text">The student is fixed for an existing assignment.</div>
                    @endif
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="school_class_id" class="form-label fw-semibold">Class</label>
                    <select name="school_class_id" id="school_class_id" class="form-select" required>
                        <option value="">Select class</option>
                        @foreach ($classes as $schoolClass)
                            <option value="{{ $schoolClass->id }}" @selected((string) old('school_class_id', $editAssignment?->school_class_id) === (string) $schoolClass->id)>
                                {{ $schoolClass->name }}{{ $schoolClass->status === 'inactive' ? ' (Inactive)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="school_section_id" class="form-label fw-semibold">Section</label>
                    <select name="school_section_id" id="school_section_id" class="form-select" required>
                        <option value="">Select section</option>
                        @foreach ($classes as $schoolClass)
                            @foreach ($schoolClass->sections as $section)
                                <option value="{{ $section->id }}" data-class-id="{{ $schoolClass->id }}"
                                    @selected((string) old('school_section_id', $editAssignment?->school_section_id) === (string) $section->id)>
                                    {{ $section->name }} ({{ $section->capacity }} seats){{ $section->status === 'inactive' ? ' - Inactive' : '' }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>{{ $editAssignment ? 'Update Assignment' : 'Assign Student' }}</button>
                @if ($editAssignment)<a href="{{ route('super_admin.student_assignments.create') }}" class="btn btn-outline-secondary">Cancel Edit</a>@endif
            </div>
        </form>
    </div>
@endif

<h5 class="text-navy fw-bold mb-3">Recent Assignments</h5>
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Student</th><th>Academic Year</th><th>Class</th><th>Section</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($recentAssignments as $assignment)
                <tr>
                    <td>{{ $assignment->student->admission_number }} - {{ $assignment->student->full_name }}</td>
                    <td>{{ $assignment->academic_year }}</td>
                    <td>{{ $assignment->schoolClass->name }}</td>
                    <td>{{ $assignment->schoolSection->name }}</td>
                    <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('super_admin.student_assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        @if ($assignment->status === 'assigned')
                            <a href="{{ route('super_admin.student_assignments.create', ['edit' => $assignment->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No student assignments have been created.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
@parent
<script>
    const assignmentClass = document.getElementById('school_class_id');
    const assignmentSection = document.getElementById('school_section_id');

    function filterAssignmentSections() {
        if (!assignmentClass || !assignmentSection) return;
        const selectedClass = assignmentClass.value;
        let selectedStillVisible = false;

        Array.from(assignmentSection.options).forEach((option) => {
            if (!option.value) return;
            const visible = option.dataset.classId === selectedClass;
            option.hidden = !visible;
            option.disabled = !visible;
            if (visible && option.selected) selectedStillVisible = true;
        });

        if (!selectedStillVisible) assignmentSection.value = '';
    }

    assignmentClass?.addEventListener('change', filterAssignmentSections);
    filterAssignmentSections();
</script>
@endsection
