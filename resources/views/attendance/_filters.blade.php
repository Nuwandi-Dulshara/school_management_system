<div class="col-xl-3 col-md-6">
    <label for="filter_date" class="form-label fw-semibold">Date</label>
    <input type="date" name="date" id="filter_date" class="form-control" value="{{ request('date') }}">
</div>
<div class="col-xl-3 col-md-6">
    <label for="filter_class_id" class="form-label fw-semibold">Class</label>
    <select name="class_id" id="filter_class_id" class="form-select">
        <option value="">All classes</option>
        @foreach ($classes as $schoolClass)
            <option value="{{ $schoolClass->id }}" @selected((string) request('class_id') === (string) $schoolClass->id)>
                {{ $schoolClass->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="col-xl-3 col-md-6">
    <label for="filter_section_id" class="form-label fw-semibold">Section</label>
    <select name="section_id" id="filter_section_id" class="form-select">
        <option value="">All sections</option>
        @foreach ($classes as $schoolClass)
            @foreach ($schoolClass->sections as $section)
                <option value="{{ $section->id }}" data-class-id="{{ $schoolClass->id }}"
                    @selected((string) request('section_id') === (string) $section->id)>
                    {{ $schoolClass->name }} - {{ $section->name }}
                </option>
            @endforeach
        @endforeach
    </select>
</div>
