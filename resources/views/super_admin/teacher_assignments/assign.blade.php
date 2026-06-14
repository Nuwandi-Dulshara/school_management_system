@extends('layouts.super_admin', ['pageTitle' => 'Assign Teacher'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>{{ $editAssignment ? 'Edit Teacher Assignment' : 'Assign Teacher' }}</h1>
        <p>Assign a teacher to a subject offered by a selected class.</p>
    </div>
    <a href="{{ route('super_admin.teacher_assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-list-ul me-2"></i>Assignment List</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@if ($teachers->isEmpty() || $classes->isEmpty() || $classSubjects->isEmpty())
    <div class="alert alert-warning">An active teacher and at least one class-subject mapping are required before assigning teachers.</div>
@else
    <div class="border rounded-3 p-4 mb-4">
        <h5 class="text-navy fw-bold mb-3">{{ $editAssignment ? 'Update Teaching Assignment' : 'New Teaching Assignment' }}</h5>
        <form method="POST" action="{{ $editAssignment ? route('super_admin.teacher_assignments.update', $editAssignment) : route('super_admin.teacher_assignments.store') }}">
            @csrf
            @if ($editAssignment) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="teacher_id" class="form-label fw-semibold">Teacher</label>
                    <select name="teacher_id" id="teacher_id" class="form-select" required>
                        <option value="">Select teacher</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $editAssignment?->teacher_id) === (string) $teacher->id)>
                                {{ $teacher->employee_number }} - {{ $teacher->full_name }}{{ $teacher->status === 'inactive' ? ' (Inactive)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
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
                    <label for="subject_id" class="form-label fw-semibold">Subject</label>
                    <select name="subject_id" id="subject_id" class="form-select" required>
                        <option value="">Select subject</option>
                        @foreach ($classSubjects as $classSubject)
                            <option value="{{ $classSubject->subject_id }}" data-class-id="{{ $classSubject->school_class_id }}"
                                @selected((string) old('subject_id', $editAssignment?->subject_id) === (string) $classSubject->subject_id && (string) old('school_class_id', $editAssignment?->school_class_id) === (string) $classSubject->school_class_id)>
                                {{ $classSubject->subject->code }} - {{ $classSubject->subject->name }}{{ $classSubject->status === 'inactive' ? ' (Not offered)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        @foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $editAssignment?->status ?? 'active') === $value)>{{ $label }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>{{ $editAssignment ? 'Update Assignment' : 'Assign Teacher' }}</button>
                @if ($editAssignment)<a href="{{ route('super_admin.teacher_assignments.create') }}" class="btn btn-outline-secondary">Cancel Edit</a>@endif
            </div>
        </form>
    </div>
@endif

<h5 class="text-navy fw-bold mb-3">Recent Assignments</h5>
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Teacher</th><th>Class</th><th>Subject</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($recentAssignments as $assignment)
                <tr>
                    <td>{{ $assignment->teacher->full_name }}</td>
                    <td>{{ $assignment->schoolClass->name }}</td>
                    <td>{{ $assignment->subject->code }} - {{ $assignment->subject->name }}</td>
                    <td><span class="status-badge status-{{ $assignment->status }}">{{ $statuses[$assignment->status] }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('super_admin.teacher_assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('super_admin.teacher_assignments.create', ['edit' => $assignment->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No teaching assignments have been created.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
@parent
<script>
    const teachingClass = document.getElementById('school_class_id');
    const teachingSubject = document.getElementById('subject_id');

    function filterTeachingSubjects() {
        if (!teachingClass || !teachingSubject) return;
        const classId = teachingClass.value;
        let selectedVisible = false;
        Array.from(teachingSubject.options).forEach((option) => {
            if (!option.value) return;
            const visible = option.dataset.classId === classId;
            option.hidden = !visible;
            option.disabled = !visible;
            if (visible && option.selected) selectedVisible = true;
        });
        if (!selectedVisible) teachingSubject.value = '';
    }

    teachingClass?.addEventListener('change', filterTeachingSubjects);
    filterTeachingSubjects();
</script>
@endsection
