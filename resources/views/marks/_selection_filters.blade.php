<div class="col-lg-3">
    <label class="form-label fw-semibold">Exam</label>
    <select name="exam_id" id="exam_id" class="form-select">
        <option value="">Select exam</option>
        @foreach ($exams as $exam)
            <option value="{{ $exam->id }}" data-class-id="{{ $exam->school_class_id }}" data-section-id="{{ $exam->school_section_id }}"
                @selected((string) ($filters['exam_id'] ?? request('exam_id')) === (string) $exam->id)>
                {{ $exam->exam_name }} ({{ $exam->academic_year }})
            </option>
        @endforeach
    </select>
</div>
<div class="col-lg-3">
    <label class="form-label fw-semibold">Class</label>
    <select name="class_id" id="class_id" class="form-select">
        <option value="">Select class</option>
        @foreach ($classes as $class)
            <option value="{{ $class->id }}" @selected((string) ($filters['class_id'] ?? request('class_id')) === (string) $class->id)>{{ $class->name }}</option>
        @endforeach
    </select>
</div>
<div class="col-lg-3">
    <label class="form-label fw-semibold">Section</label>
    <select name="section_id" id="section_id" class="form-select">
        <option value="">Select section</option>
        @foreach ($classes as $class)
            @foreach ($class->sections as $section)
                <option value="{{ $section->id }}" data-class-id="{{ $class->id }}"
                    @selected((string) ($filters['section_id'] ?? request('section_id')) === (string) $section->id)>
                    {{ $class->name }} - {{ $section->name }}
                </option>
            @endforeach
        @endforeach
    </select>
</div>
@isset($subjects)
<div class="col-lg-3">
    <label class="form-label fw-semibold">Subject</label>
    <select name="subject_id" id="subject_id" class="form-select">
        <option value="">Select subject</option>
        @foreach ($subjects as $subject)
            <option value="{{ $subject->id }}" @selected((string) ($filters['subject_id'] ?? request('subject_id')) === (string) $subject->id)>{{ $subject->code }} - {{ $subject->name }}</option>
        @endforeach
    </select>
</div>
@endisset
