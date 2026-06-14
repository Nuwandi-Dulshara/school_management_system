@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-3">
    <div class="col-md-6"><label class="form-label fw-semibold">Fee Type Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $feeType->name ?? '') }}" required></div>
    <div class="col-md-2"><label class="form-label fw-semibold">Amount</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $feeType->amount ?? '') }}" required></div>
    <div class="col-md-2"><label class="form-label fw-semibold">Frequency</label><select name="frequency" class="form-select" required>@foreach ($frequencies as $value => $label)<option value="{{ $value }}" @selected(old('frequency', $feeType->frequency ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select" required>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $feeType->status ?? 'active') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" rows="4" class="form-control">{{ old('description', $feeType->description ?? '') }}</textarea></div>
</div>
<div class="d-flex gap-2 mt-4"><button class="btn btn-academic">{{ $submitLabel }}</button><a href="{{ route('fees.types.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
