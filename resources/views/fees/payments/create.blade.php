@extends('layouts.super_admin', ['pageTitle' => 'Record Payment'])
@section('super_admin_content')
<div class="page-heading"><div><h1>Record Payment</h1><p>Record a payment and generate a printable receipt.</p></div></div>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-3 mb-4"><div class="col-md-3"><div class="detail-label">Student</div><p class="detail-value">{{ $assignment->student->full_name }}</p></div><div class="col-md-3"><div class="detail-label">Fee Type</div><p class="detail-value">{{ $assignment->feeType->name }}</p></div><div class="col-md-2"><div class="detail-label">Total</div><p class="detail-value">{{ number_format($assignment->assigned_amount, 2) }}</p></div><div class="col-md-2"><div class="detail-label">Already Paid</div><p class="detail-value">{{ number_format($assignment->paid_amount, 2) }}</p></div><div class="col-md-2"><div class="detail-label">Balance</div><p class="detail-value">{{ number_format($assignment->balance_amount, 2) }}</p></div></div>
<form method="POST" action="{{ route('fees.payments.store', $assignment) }}" class="row g-3">@csrf
    <div class="col-md-3"><label class="form-label fw-semibold">Payment Amount</label><input type="number" step="0.01" min="0.01" max="{{ $assignment->balance_amount }}" name="payment_amount" class="form-control" value="{{ old('payment_amount') }}" required></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Payment Date</label><input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Payment Method</label><select name="payment_method" class="form-select">@foreach ($methods as $value => $label)<option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Reference Number</label><input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}"></div>
    <div class="col-12"><label class="form-label fw-semibold">Remarks</label><textarea name="remarks" rows="3" class="form-control">{{ old('remarks') }}</textarea></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-academic">Record Payment</button><a href="{{ route('fees.assignments.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form>
@endsection
