@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label for="school_class_id" class="form-label fw-semibold">Class</label>
        <select name="school_class_id" id="school_class_id" class="form-select @error('school_class_id') is-invalid @enderror" required>
            <option value="">Select a class</option>
            @foreach ($classes as $classOption)
                <option value="{{ $classOption->id }}" @selected((string) old('school_class_id', $schoolSection->school_class_id ?? request('class_id')) === (string) $classOption->id)>{{ $classOption->name }}</option>
            @endforeach
        </select>
        @error('school_class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="name" class="form-label fw-semibold">Section Name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $schoolSection->name ?? '') }}" placeholder="A" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="capacity" class="form-label fw-semibold">Section Capacity</label>
        <input type="number" name="capacity" id="capacity" min="1" max="1000" class="form-control @error('capacity') is-invalid @enderror"
            value="{{ old('capacity', $schoolSection->capacity ?? 40) }}" required>
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $schoolSection->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>{{ $submitLabel }}</button>
    <a href="{{ route('super_admin.sections.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
