@extends('layouts.super_admin', ['pageTitle' => 'Parent / Guardian Details'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Parent / Guardian Details</h1>
        <p>Manage contact information for {{ $student->full_name }}.</p>
    </div>
    <a href="{{ route('super_admin.students.show', $student) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Student Profile
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('super_admin.students.guardians.update', $student) }}">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6">
            <label for="guardian_name" class="form-label fw-semibold">Parent / Guardian Name</label>
            <input type="text" name="guardian_name" id="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror"
                value="{{ old('guardian_name', $student->guardian?->guardian_name) }}" required>
            @error('guardian_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="relationship" class="form-label fw-semibold">Relationship</label>
            <input type="text" name="relationship" id="relationship" class="form-control @error('relationship') is-invalid @enderror"
                value="{{ old('relationship', $student->guardian?->relationship) }}" required>
            @error('relationship')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
            <input type="text" name="contact_number" id="contact_number" class="form-control @error('contact_number') is-invalid @enderror"
                value="{{ old('contact_number', $student->guardian?->contact_number) }}" required>
            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $student->guardian?->email) }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="address" class="form-label fw-semibold">Address</label>
            <textarea name="address" id="address" rows="4" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $student->guardian?->address) }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <button class="btn btn-academic mt-4" type="submit"><i class="bi bi-check-lg me-2"></i>Save Guardian Details</button>
</form>
@endsection
