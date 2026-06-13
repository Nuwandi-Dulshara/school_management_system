@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label fw-semibold">Class Name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $schoolClass->name ?? '') }}" placeholder="Grade 10" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="capacity" class="form-label fw-semibold">Class Capacity</label>
        <input type="number" name="capacity" id="capacity" min="1" max="5000"
            class="form-control @error('capacity') is-invalid @enderror"
            value="{{ old('capacity', $schoolClass->capacity ?? 120) }}" required>
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $schoolClass->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-academic">
        <i class="bi bi-check-lg me-2"></i>{{ $submitLabel }}
    </button>
    <a href="{{ route('super_admin.classes.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
