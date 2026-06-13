@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="border rounded-3 p-4">
    <h5 class="text-navy fw-bold mb-4"><i class="bi bi-person-vcard me-2"></i>Teacher Information</h5>
    <div class="row g-3">
        <div class="col-md-8">
            <label for="full_name" class="form-label fw-semibold">Full Name</label>
            <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror"
                value="{{ old('full_name', $teacher->full_name ?? '') }}" required>
            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="employee_number" class="form-label fw-semibold">Employee Number</label>
            <input type="text" name="employee_number" id="employee_number" class="form-control @error('employee_number') is-invalid @enderror"
                value="{{ old('employee_number', $teacher->employee_number ?? '') }}" required>
            @error('employee_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="date_of_birth" class="form-label fw-semibold">Date of Birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                value="{{ old('date_of_birth', isset($teacher) ? $teacher->date_of_birth->format('Y-m-d') : '') }}" required>
            @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="gender" class="form-label fw-semibold">Gender</label>
            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                <option value="">Select gender</option>
                @foreach ($genders as $value => $label)
                    <option value="{{ $value }}" @selected(old('gender', $teacher->gender ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
            <input type="text" name="contact_number" id="contact_number" class="form-control @error('contact_number') is-invalid @enderror"
                value="{{ old('contact_number', $teacher->contact_number ?? '') }}" required>
            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $teacher->email ?? '') }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="qualification" class="form-label fw-semibold">Qualification</label>
            <input type="text" name="qualification" id="qualification" class="form-control @error('qualification') is-invalid @enderror"
                value="{{ old('qualification', $teacher->qualification ?? '') }}" required>
            @error('qualification')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="experience" class="form-label fw-semibold">Experience (Years)</label>
            <input type="number" name="experience" id="experience" min="0" max="60" class="form-control @error('experience') is-invalid @enderror"
                value="{{ old('experience', $teacher->experience ?? 0) }}" required>
            @error('experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="main_subject" class="form-label fw-semibold">Main Subject</label>
            <input type="text" name="main_subject" id="main_subject" class="form-control @error('main_subject') is-invalid @enderror"
                value="{{ old('main_subject', $teacher->main_subject ?? '') }}" required>
            @error('main_subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="joining_date" class="form-label fw-semibold">Joining Date</label>
            <input type="date" name="joining_date" id="joining_date" class="form-control @error('joining_date') is-invalid @enderror"
                value="{{ old('joining_date', isset($teacher) ? $teacher->joining_date->format('Y-m-d') : '') }}" required>
            @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $teacher->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="address" class="form-label fw-semibold">Address</label>
            <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $teacher->address ?? '') }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-academic">
        <i class="bi bi-check-lg me-2"></i>{{ $submitLabel }}
    </button>
    <a href="{{ route('super_admin.teachers.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
