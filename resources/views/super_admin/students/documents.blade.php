@extends('layouts.super_admin', ['pageTitle' => 'Student Documents'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Student Documents</h1>
        <p>Manage simple document records for {{ $student->full_name }}.</p>
    </div>
    <a href="{{ route('super_admin.students.show', $student) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Student Profile
    </a>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle-fill me-2"></i>
    This page stores document information only. File uploads can be added later.
</div>

<div class="border rounded-3 p-4 mb-4">
    <h5 class="text-navy fw-bold mb-4">Add Document Record</h5>
    <form method="POST" action="{{ route('super_admin.students.documents.store', $student) }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-5">
                <label for="document_name" class="form-label fw-semibold">Document Name</label>
                <input type="text" name="document_name" id="document_name" class="form-control @error('document_name') is-invalid @enderror"
                    value="{{ old('document_name') }}" required>
                @error('document_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="document_type" class="form-label fw-semibold">Document Type</label>
                <input type="text" name="document_type" id="document_type" class="form-control @error('document_type') is-invalid @enderror"
                    value="{{ old('document_type') }}" placeholder="Birth Certificate" required>
                @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label for="uploaded_date" class="form-label fw-semibold">Uploaded Date</label>
                <input type="date" name="uploaded_date" id="uploaded_date" class="form-control @error('uploaded_date') is-invalid @enderror"
                    value="{{ old('uploaded_date', now()->format('Y-m-d')) }}" required>
                @error('uploaded_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <button class="btn btn-academic mt-3" type="submit"><i class="bi bi-plus-lg me-2"></i>Add Document</button>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Document Name</th>
                <th>Document Type</th>
                <th>Uploaded Date</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($student->documents as $document)
                <tr>
                    <td class="fw-semibold"><i class="bi bi-file-earmark-text text-navy me-2"></i>{{ $document->document_name }}</td>
                    <td>{{ $document->document_type }}</td>
                    <td>{{ $document->uploaded_date->format('F j, Y') }}</td>
                    <td class="text-end">
                        <span class="btn btn-sm btn-outline-secondary disabled" title="File upload is not enabled">
                            <i class="bi bi-download"></i>
                        </span>
                        <form method="POST" action="{{ route('super_admin.students.documents.destroy', [$student, $document]) }}"
                            class="d-inline" onsubmit="return confirm('Remove this document record?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <i class="bi bi-folder2-open"></i>
                            <h5>No document records</h5>
                            <p class="mb-0">Add the first document using the form above.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
