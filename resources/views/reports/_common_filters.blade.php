@if(in_array('academic_year', $fields, true))
<div class="col-md-3"><label class="form-label">Academic Year</label><select name="academic_year" class="form-select"><option value="">All years</option>@foreach($academicYears as $year)<option value="{{ $year }}" @selected(request('academic_year') === $year)>{{ $year }}</option>@endforeach</select></div>
@endif
@if(in_array('class_id', $fields, true))
<div class="col-md-3"><label class="form-label">Class</label><select name="class_id" class="form-select"><option value="">All classes</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>{{ $class->name }}</option>@endforeach</select></div>
@endif
@if(in_array('section_id', $fields, true))
<div class="col-md-3"><label class="form-label">Section</label><select name="section_id" class="form-select"><option value="">All sections</option>@foreach($sections as $section)<option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $section->schoolClass?->name }} {{ $section->name }}</option>@endforeach</select></div>
@endif
@if(in_array('student_id', $fields, true))
<div class="col-md-3"><label class="form-label">Student</label><select name="student_id" class="form-select"><option value="">All students</option>@foreach($studentsFilter as $student)<option value="{{ $student->id }}" @selected((string) request('student_id') === (string) $student->id)>{{ $student->full_name }}</option>@endforeach</select></div>
@endif
@if(in_array('subject_id', $fields, true))
<div class="col-md-3"><label class="form-label">Subject</label><select name="subject_id" class="form-select"><option value="">All subjects</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
@endif
