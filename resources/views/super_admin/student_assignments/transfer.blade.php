@extends('layouts.super_admin', ['pageTitle' => 'Student Transfer'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Student Transfer</h1>
        <p>Move an assigned student to another class or section in the same academic year.</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@if ($selectedAssignment)
    <div class="border rounded-3 p-4 mb-4">
        <h5 class="text-navy fw-bold mb-3">Transfer {{ $selectedAssignment->student->full_name }}</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="detail-label">Admission Number</div><p class="detail-value">{{ $selectedAssignment->student->admission_number }}</p></div>
            <div class="col-md-3"><div class="detail-label">Current Class</div><p class="detail-value">{{ $selectedAssignment->schoolClass->name }}</p></div>
            <div class="col-md-3"><div class="detail-label">Current Section</div><p class="detail-value">{{ $selectedAssignment->schoolSection->name }}</p></div>
            <div class="col-md-3"><div class="detail-label">Academic Year</div><p class="detail-value">{{ $selectedAssignment->academic_year }}</p></div>
        </div>
        <form method="POST" action="{{ route('super_admin.student_assignments.transfer', $selectedAssignment) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="school_class_id" class="form-label fw-semibold">New Class</label>
                    <select name="school_class_id" id="school_class_id" class="form-select" required>
                        <option value="">Select a class</option>
                        @foreach ($classes->where('status', 'active') as $schoolClass)
                            <option value="{{ $schoolClass->id }}" @selected((string) old('school_class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="school_section_id" class="form-label fw-semibold">New Section</label>
                    <select name="school_section_id" id="school_section_id" class="form-select" required>
                        <option value="">Select a section</option>
                        @foreach ($classes as $schoolClass)
                            @foreach ($schoolClass->sections->where('status', 'active') as $section)
                                <option value="{{ $section->id }}" data-class-id="{{ $schoolClass->id }}" @selected((string) old('school_section_id') === (string) $section->id)>{{ $section->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-academic w-100"><i class="bi bi-arrow-left-right me-2"></i>Transfer</button>
                </div>
            </div>
        </form>
    </div>
@endif

<form method="GET" action="{{ route('super_admin.student_assignments.transfers') }}" class="row g-2 mb-4">
    <div class="col-lg-4"><div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, admission no. or student ID"></div></div>
    <div class="col-lg-3"><select name="academic_year" class="form-select"><option value="">All academic years</option>@foreach ($academicYears as $year)<option value="{{ $year }}" @selected(request('academic_year') === $year)>{{ $year }}</option>@endforeach</select></div>
    <div class="col-lg-3"><select name="class_id" class="form-select"><option value="">All classes</option>@foreach ($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected((string) request('class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach</select></div>
    <div class="col-lg-2 d-flex gap-2"><button class="btn btn-academic flex-grow-1">Filter</button><a href="{{ route('super_admin.student_assignments.transfers') }}" class="btn btn-outline-secondary">Clear</a></div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Student</th><th>Academic Year</th><th>Current Class</th><th>Current Section</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
            @forelse ($assignments as $assignment)
                <tr>
                    <td><div class="fw-semibold text-navy">{{ $assignment->student->full_name }}</div><small class="text-muted">{{ $assignment->student->admission_number }}</small></td>
                    <td>{{ $assignment->academic_year }}</td>
                    <td>{{ $assignment->schoolClass->name }}</td>
                    <td>{{ $assignment->schoolSection->name }}</td>
                    <td><span class="status-badge status-assigned">Assigned</span></td>
                    <td class="text-end"><a href="{{ route('super_admin.student_assignments.transfers', array_filter(['assignment' => $assignment->id, 'academic_year' => request('academic_year'), 'class_id' => request('class_id'), 'search' => request('search')])) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-arrow-left-right me-1"></i>Transfer</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No assigned students are available for transfer.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($assignments->hasPages())<div class="mt-4">{{ $assignments->links() }}</div>@endif
@endsection

@section('scripts')
@parent
<script>
    const transferClass = document.getElementById('school_class_id');
    const transferSection = document.getElementById('school_section_id');

    function filterTransferSections() {
        if (!transferClass || !transferSection) return;
        const selectedClass = transferClass.value;
        Array.from(transferSection.options).forEach((option) => {
            if (!option.value) return;
            const visible = option.dataset.classId === selectedClass;
            option.hidden = !visible;
            option.disabled = !visible;
        });
        if (transferSection.selectedOptions[0]?.disabled) transferSection.value = '';
    }

    transferClass?.addEventListener('change', filterTransferSections);
    filterTransferSections();
</script>
@endsection
