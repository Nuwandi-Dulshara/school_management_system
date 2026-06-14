@extends('layouts.super_admin', ['pageTitle' => 'Update Attendance'])

@section('super_admin_content')
<div class="page-heading">
    <div><h1>Update Attendance</h1><p>Change the saved attendance status for this student.</p></div>
    <a href="{{ route('attendance.edit_list') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>
@include('attendance._alerts')

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 bg-light">
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><div class="detail-label">Student</div><div class="detail-value">{{ $attendance->student->full_name }}</div></div>
                    <div class="col-md-6"><div class="detail-label">Admission Number</div><div class="detail-value">{{ $attendance->student->admission_number }}</div></div>
                    <div class="col-md-4"><div class="detail-label">Date</div><div class="detail-value">{{ $attendance->attendance_date->format('M d, Y') }}</div></div>
                    <div class="col-md-4"><div class="detail-label">Class</div><div class="detail-value">{{ $attendance->schoolClass->name }}</div></div>
                    <div class="col-md-4"><div class="detail-label">Section</div><div class="detail-value">{{ $attendance->schoolSection->name }}</div></div>
                </div>

                <form method="POST" action="{{ route('attendance.update', $attendance) }}">
                    @csrf @method('PUT')
                    <label for="status" class="form-label fw-semibold">Attendance Status</label>
                    <select name="status" id="status" class="form-select mb-3" required>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $attendance->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-academic"><i class="bi bi-check-lg me-2"></i>Save Updated Attendance</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
