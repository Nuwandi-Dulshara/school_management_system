@extends('layouts.super_admin', ['pageTitle' => 'Fee Types'])

@section('super_admin_content')
<div class="page-heading"><div><h1>Fee Types</h1><p>Manage school fee categories, amounts, and collection frequency.</p></div><a href="{{ route('fees.types.create') }}" class="btn btn-academic"><i class="bi bi-plus-lg me-2"></i>Add Fee Type</a></div>
<form method="GET" class="row g-2 mb-4"><div class="col-md-9"><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search fee type name"></div><div class="col-md-3 d-flex gap-2"><button class="btn btn-academic flex-grow-1">Search</button><a href="{{ route('fees.types.index') }}" class="btn btn-outline-secondary">Clear</a></div></form>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Description</th><th>Amount</th><th>Frequency</th><th>Assignments</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
@forelse ($feeTypes as $feeType)<tr><td class="fw-semibold">{{ $feeType->name }}</td><td>{{ $feeType->description ? Str::limit($feeType->description, 55) : '-' }}</td><td>{{ number_format($feeType->amount, 2) }}</td><td>{{ $frequencies[$feeType->frequency] }}</td><td>{{ $feeType->assignments_count }}</td><td><span class="status-badge status-{{ $feeType->status }}">{{ $statuses[$feeType->status] }}</span></td><td><div class="d-flex justify-content-end gap-1"><a href="{{ route('fees.types.edit', $feeType) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route('fees.types.destroy', $feeType) }}" onsubmit="return confirm('Delete this fee type?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></div></td></tr>
@empty<tr><td colspan="7"><div class="empty-state"><i class="bi bi-wallet2"></i><h5>No fee types found</h5></div></td></tr>@endforelse
</tbody></table></div>
@if ($feeTypes->hasPages())<div class="mt-4">{{ $feeTypes->links() }}</div>@endif
@endsection
