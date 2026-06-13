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

@php($guardian = $student->guardian ?? null)

<div class="border rounded-3 p-4 mb-4">
    <h5 class="text-navy fw-bold mb-4"><i class="bi bi-person-vcard me-2"></i>Student Information</h5>
    <div class="row g-3">
        <div class="col-md-8">
            <label for="full_name" class="form-label fw-semibold">Full Name</label>
            <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror"
                value="{{ old('full_name', $student->full_name ?? '') }}" required>
            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="admission_number" class="form-label fw-semibold">Admission Number</label>
            <input type="text" name="admission_number" id="admission_number" class="form-control @error('admission_number') is-invalid @enderror"
                value="{{ old('admission_number', $student->admission_number ?? '') }}" required>
            @error('admission_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="date_of_birth" class="form-label fw-semibold">Date of Birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                value="{{ old('date_of_birth', isset($student) ? $student->date_of_birth->format('Y-m-d') : '') }}" required>
            @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="gender" class="form-label fw-semibold">Gender</label>
            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                <option value="">Select gender</option>
                @foreach ($genders as $value => $label)
                    <option value="{{ $value }}" @selected(old('gender', $student->gender ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
            <input type="text" name="contact_number" id="contact_number" class="form-control @error('contact_number') is-invalid @enderror"
                value="{{ old('contact_number', $student->contact_number ?? '') }}" required>
            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $student->email ?? '') }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label for="class" class="form-label fw-semibold">Class</label>
            <input type="text" name="class" id="class" class="form-control @error('class') is-invalid @enderror"
                value="{{ old('class', $student->class ?? '') }}" required>
            @error('class')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label for="section" class="form-label fw-semibold">Section</label>
            <input type="text" name="section" id="section" class="form-control @error('section') is-invalid @enderror"
                value="{{ old('section', $student->section ?? '') }}" required>
            @error('section')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="admission_date" class="form-label fw-semibold">Admission Date</label>
            <input type="date" name="admission_date" id="admission_date" class="form-control @error('admission_date') is-invalid @enderror"
                value="{{ old('admission_date', isset($student) ? $student->admission_date->format('Y-m-d') : '') }}" required>
            @error('admission_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $student->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="address" class="form-label fw-semibold">Address</label>
            <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $student->address ?? '') }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="border rounded-3 p-4">
    <h5 class="text-navy fw-bold mb-4"><i class="bi bi-people me-2"></i>Parent / Guardian Information</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="guardian_name" class="form-label fw-semibold">Parent / Guardian Name</label>
            <input type="text" name="guardian_name" id="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror"
                value="{{ old('guardian_name', $guardian->guardian_name ?? '') }}" required>
            @error('guardian_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="relationship" class="form-label fw-semibold">Relationship</label>
            <input type="text" name="relationship" id="relationship" class="form-control @error('relationship') is-invalid @enderror"
                value="{{ old('relationship', $guardian->relationship ?? '') }}" required>
            @error('relationship')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="guardian_contact_number" class="form-label fw-semibold">Contact Number</label>
            <input type="text" name="guardian_contact_number" id="guardian_contact_number" class="form-control @error('guardian_contact_number') is-invalid @enderror"
                value="{{ old('guardian_contact_number', $guardian->contact_number ?? '') }}" required>
            @error('guardian_contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="guardian_email" class="form-label fw-semibold">Email</label>
            <input type="email" name="guardian_email" id="guardian_email" class="form-control @error('guardian_email') is-invalid @enderror"
                value="{{ old('guardian_email', $guardian->email ?? '') }}">
            @error('guardian_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="guardian_address" class="form-label fw-semibold">Address</label>
            <textarea name="guardian_address" id="guardian_address" rows="3" class="form-control @error('guardian_address') is-invalid @enderror" required>{{ old('guardian_address', $guardian->address ?? '') }}</textarea>
            @error('guardian_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-academic">
        <i class="bi bi-check-lg me-2"></i>{{ $submitLabel }}
    </button>
    <a href="{{ route('super_admin.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
