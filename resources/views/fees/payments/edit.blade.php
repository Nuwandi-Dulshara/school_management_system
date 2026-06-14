@extends('layouts.super_admin', ['pageTitle' => 'Edit Payment'])
@section('super_admin_content')
<div class="page-heading"><div><h1>Edit Payment</h1><p>{{ $payment->receipt_number }} - {{ $payment->student->full_name }}</p></div></div>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('fees.payments.update', $payment) }}" class="row g-3">@csrf @method('PUT')
    <div class="col-md-3"><label class="form-label fw-semibold">Payment Amount</label><input type="number" step="0.01" min="0.01" name="payment_amount" class="form-control" value="{{ old('payment_amount', $payment->payment_amount) }}" required></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Payment Date</label><input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', $payment->payment_date->toDateString()) }}" required></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Payment Method</label><select name="payment_method" class="form-select">@foreach ($methods as $value => $label)<option value="{{ $value }}" @selected(old('payment_method', $payment->payment_method) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Reference Number</label><input type="text" name="reference_number" class="form-control" value="{{ old('reference_number', $payment->reference_number) }}"></div>
    <div class="col-12"><label class="form-label fw-semibold">Remarks</label><textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $payment->remarks) }}</textarea></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-academic">Update Payment</button><a href="{{ route('fees.payments.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form>
@endsection
