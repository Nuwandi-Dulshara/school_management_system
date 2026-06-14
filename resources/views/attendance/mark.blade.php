@extends('layouts.super_admin', ['pageTitle' => 'Mark Attendance'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Mark Attendance</h1>
        <p>Select a date, class, and section to load active students.</p>
    </div>
    <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary"><i class="bi bi-list-check me-2"></i>Attendance List</a>
</div>

@include('attendance._alerts')

<form method="GET" action="{{ route('attendance.mark') }}" class="row g-3 align-items-end mb-4">
    <div class="col-lg-3 col-md-6">
        <label for="attendance_date_filter" class="form-label fw-semibold">Date</label>
        <input type="date" name="date" id="attendance_date_filter" class="form-control" value="{{ $selectedDate }}" required>
    </div>
    <div class="col-lg-3 col-md-6">
        <label for="mark_class_id" class="form-label fw-semibold">Class</label>
        <select name="class_id" id="mark_class_id" class="form-select" required>
            <option value="">Select class</option>
            @foreach ($classes as $schoolClass)
                <option value="{{ $schoolClass->id }}" @selected((string) $selectedClassId === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3 col-md-6">
        <label for="mark_section_id" class="form-label fw-semibold">Section</label>
        <select name="section_id" id="mark_section_id" class="form-select" required>
            <option value="">Select section</option>
            @foreach ($classes as $schoolClass)
                @foreach ($schoolClass->sections as $section)
                    <option value="{{ $section->id }}" data-class-id="{{ $schoolClass->id }}" @selected((string) $selectedSectionId === (string) $section->id)>{{ $section->name }}</option>
                @endforeach
            @endforeach
        </select>
    </div>
    <div class="col-lg-3 col-md-6">
        <button class="btn btn-academic w-100"><i class="bi bi-people me-2"></i>Load Students</button>
    </div>
</form>

@if ($selectedClassId && $selectedSectionId)
    @if ($existingCount > 0)
        <div class="alert alert-warning d-flex justify-content-between align-items-center gap-3">
            <span>Attendance already exists for {{ $existingCount }} student(s) on this date.</span>
            <a href="{{ route('attendance.edit_list', ['date' => $selectedDate, 'class_id' => $selectedClassId, 'section_id' => $selectedSectionId]) }}" class="btn btn-sm btn-outline-dark">Edit Attendance</a>
        </div>
    @endif

    <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <input type="hidden" name="attendance_date" value="{{ $selectedDate }}">
        <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="school_section_id" value="{{ $selectedSectionId }}">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="text-muted">{{ $students->count() }} active student(s)</div>
            @if ($students->isNotEmpty() && $existingCount === 0)
                <div class="btn-group btn-group-sm" role="group" aria-label="Mark all students">
                    @foreach ($statuses as $value => $label)
                        <button type="button" class="btn btn-outline-secondary" data-mark-all="{{ $value }}">All {{ $label }}</button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Admission No.</th><th>Student</th><th>Attendance Status</th></tr></thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>{{ $student->admission_number }}</td>
                            <td class="fw-semibold text-navy">{{ $student->full_name }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($statuses as $value => $label)
                                        <div class="form-check">
                                            <input class="form-check-input attendance-option" type="radio"
                                                name="attendance[{{ $student->id }}]"
                                                id="attendance_{{ $student->id }}_{{ $value }}"
                                                value="{{ $value }}"
                                                @checked(old("attendance.{$student->id}", 'present') === $value)
                                                required>
                                            <label class="form-check-label" for="attendance_{{ $student->id }}_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><i class="bi bi-people"></i><h5>No active students found</h5><p class="mb-0">Assign active students to this class and section first.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->isNotEmpty() && $existingCount === 0)
            <div class="text-end mt-3">
                <button class="btn btn-academic"><i class="bi bi-check2-circle me-2"></i>Save Attendance</button>
            </div>
        @endif
    </form>
@endif
@endsection

@section('scripts')
@parent
@include('attendance._class_section_script', ['classSelectId' => 'mark_class_id', 'sectionSelectId' => 'mark_section_id'])
<script>
    document.querySelectorAll('[data-mark-all]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll(`.attendance-option[value="${button.dataset.markAll}"]`)
                .forEach((input) => input.checked = true);
        });
    });
</script>
@endsection
