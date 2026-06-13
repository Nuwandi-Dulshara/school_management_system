@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-3">
        <label for="code" class="form-label fw-semibold">Subject Code</label>
        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $subject->code ?? '') }}" placeholder="MAT-101" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="name" class="form-label fw-semibold">Subject Name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $subject->name ?? '') }}" placeholder="Mathematics" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $subject->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Description</label>
        <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror"
            placeholder="Short description of the subject">{{ old('description', $subject->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>{{ $submitLabel }}</button>
    <a href="{{ route('super_admin.subjects.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
