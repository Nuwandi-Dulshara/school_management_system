@extends('layouts.super_admin', ['pageTitle' => 'Class Student List'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>{{ $schoolClass->name }} Student List</h1>
        <p>Assign students, filter by section, and move students between sections.</p>
    </div>
    <a href="{{ route('super_admin.classes.show', $schoolClass) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Class Details</a>
</div>

@if ($availableStudents->isNotEmpty())
    <div class="border rounded-3 p-4 mb-4">
        <h5 class="text-navy fw-bold mb-3">Assign Student</h5>
        <form method="POST" id="assign-student-form" class="row g-3"
            data-action-template="{{ route('super_admin.classes.students.assign', [$schoolClass, '__STUDENT__']) }}">
            @csrf
            @method('PATCH')
            <div class="col-md-5">
                <label for="student_id" class="form-label fw-semibold">Student</label>
                <select id="student_id" class="form-select" required>
                    <option value="">Select an unassigned student</option>
                    @foreach ($availableStudents as $availableStudent)
                        <option value="{{ $availableStudent->id }}">{{ $availableStudent->admission_number }} - {{ $availableStudent->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="new_school_section_id" class="form-label fw-semibold">Section</label>
                <select name="school_section_id" id="new_school_section_id" class="form-select" required>
                    <option value="">Select a section</option>
                    @foreach ($sections->where('status', 'active') as $section)
                        <option value="{{ $section->id }}">{{ $section->name }} (capacity {{ $section->capacity }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-academic w-100" type="submit">Assign Student</button>
            </div>
        </form>
    </div>
@endif

<form method="GET" action="{{ route('super_admin.classes.students', $schoolClass) }}" class="row g-2 mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Student name or admission number">
        </div>
    </div>
    <div class="col-md-3">
        <select name="section_id" class="form-select">
            <option value="">All sections</option>
            @foreach ($sections as $section)
                <option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $section->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-academic flex-grow-1">Filter</button>
        @if (request()->hasAny(['search', 'section_id']))<a href="{{ route('super_admin.classes.students', $schoolClass) }}" class="btn btn-outline-secondary">Clear</a>@endif
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Admission Number</th><th>Student Name</th><th>Section</th><th>Status</th><th>Move Section</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student->admission_number }}</td>
                    <td><a href="{{ route('super_admin.students.show', $student) }}" class="fw-semibold text-navy text-decoration-none">{{ $student->full_name }}</a></td>
                    <td>{{ $student->schoolSection?->name ?? 'Unassigned' }}</td>
                    <td><span class="status-badge status-{{ $student->status }}">{{ ucfirst($student->status) }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('super_admin.classes.students.assign', [$schoolClass, $student]) }}" class="d-flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="school_section_id" class="form-select form-select-sm" required>
                                @foreach ($sections->where('status', 'active') as $section)
                                    <option value="{{ $section->id }}" @selected($student->school_section_id === $section->id)>{{ $section->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-academic">Move</button>
                        </form>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('super_admin.classes.students.remove', [$schoolClass, $student]) }}"
                            onsubmit="return confirm('Remove this student from the class?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-people"></i><h5>No assigned students</h5><p class="mb-0">Assign students using the form above.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($students->hasPages())<div class="mt-4">{{ $students->links() }}</div>@endif
@endsection

@section('scripts')
@parent
<script>
    const assignmentForm = document.getElementById('assign-student-form');

    if (assignmentForm) {
        assignmentForm.addEventListener('submit', (event) => {
            const studentId = document.getElementById('student_id').value;

            if (!studentId) {
                event.preventDefault();
                return;
            }

            assignmentForm.action = assignmentForm.dataset.actionTemplate.replace('__STUDENT__', studentId);
        });
    }
</script>
@endsection
