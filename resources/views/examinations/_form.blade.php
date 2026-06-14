@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="exam_name" class="form-label fw-semibold">Exam Name</label>
        <input type="text" name="exam_name" id="exam_name" class="form-control @error('exam_name') is-invalid @enderror"
            value="{{ old('exam_name', $exam->exam_name ?? '') }}" required>
        @error('exam_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="exam_type_id" class="form-label fw-semibold">Exam Type</label>
        <select name="exam_type_id" id="exam_type_id" class="form-select @error('exam_type_id') is-invalid @enderror" required>
            <option value="">Select exam type</option>
            @foreach ($examTypes as $type)
                <option value="{{ $type->id }}" @selected((string) old('exam_type_id', $exam->exam_type_id ?? '') === (string) $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        @error('exam_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="academic_year" class="form-label fw-semibold">Academic Year</label>
        <select name="academic_year" id="academic_year" class="form-select @error('academic_year') is-invalid @enderror" required>
            <option value="">Select academic year</option>
            @foreach ($academicYears as $year)
                <option value="{{ $year }}" @selected(old('academic_year', $exam->academic_year ?? '') === $year)>{{ $year }}</option>
            @endforeach
        </select>
        @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="school_class_id" class="form-label fw-semibold">Class</label>
        <select name="school_class_id" id="school_class_id" class="form-select @error('school_class_id') is-invalid @enderror" required>
            <option value="">Select class</option>
            @foreach ($classes as $schoolClass)
                <option value="{{ $schoolClass->id }}" @selected((string) old('school_class_id', $exam->school_class_id ?? '') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>
            @endforeach
        </select>
        @error('school_class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="school_section_id" class="form-label fw-semibold">Section</label>
        <select name="school_section_id" id="school_section_id" class="form-select @error('school_section_id') is-invalid @enderror" required>
            <option value="">Select section</option>
            @foreach ($classes as $schoolClass)
                @foreach ($schoolClass->sections as $section)
                    <option value="{{ $section->id }}" data-class-id="{{ $schoolClass->id }}"
                        @selected((string) old('school_section_id', $exam->school_section_id ?? '') === (string) $section->id)>
                        {{ $schoolClass->name }} - {{ $section->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
        @error('school_section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="start_date" class="form-label fw-semibold">Start Date</label>
        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
            value="{{ old('start_date', isset($exam) ? $exam->start_date->toDateString() : '') }}" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="end_date" class="form-label fw-semibold">End Date</label>
        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
            value="{{ old('end_date', isset($exam) ? $exam->end_date->toDateString() : '') }}" required>
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $exam->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Description</label>
        <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $exam->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>{{ $submitLabel }}</button>
    <a href="{{ route('examinations.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('exam_scripts')
<script>
    const examClass = document.getElementById('school_class_id');
    const examSection = document.getElementById('school_section_id');
    const filterExamSections = () => {
        const classId = examClass.value;
        [...examSection.options].forEach((option, index) => {
            if (index === 0) return;
            option.hidden = option.dataset.classId !== classId;
        });
        if (examSection.selectedOptions[0]?.hidden) examSection.value = '';
    };
    examClass.addEventListener('change', filterExamSections);
    filterExamSections();
</script>
@endpush
