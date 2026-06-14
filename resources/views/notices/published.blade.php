@extends('layouts.super_admin', ['pageTitle' => 'Published Notices'])
@section('super_admin_content')
<div class="page-heading"><div><h1>Published Notices</h1><p>Current announcements available for your role and assigned classes.</p></div></div>
<div class="row g-4">
@forelse ($notices as $notice)
<div class="col-lg-6"><div class="card h-100 border-0 shadow-sm {{ $notice->priority === 'urgent' ? 'border-start border-danger border-4' : ($notice->priority === 'important' ? 'border-start border-warning border-4' : '') }}"><div class="card-body p-4"><div class="d-flex justify-content-between gap-3 mb-3"><span class="status-badge priority-{{ $notice->priority }}">{{ $priorities[$notice->priority] }}</span><small class="text-muted">{{ $notice->publish_date->format('M d, Y') }}</small></div><h4 class="text-navy">{{ $notice->title }}</h4><p class="text-muted">{{ Str::limit($notice->message, 180) }}</p><div class="d-flex justify-content-between align-items-center mt-4"><small class="text-muted">{{ $audiences[$notice->audience] }}</small><a href="{{ route('notices.show', $notice) }}" class="btn btn-sm btn-outline-primary">View More</a></div></div></div></div>
@empty
<div class="col-12"><div class="empty-state"><i class="bi bi-megaphone"></i><h5>No published notices</h5><p class="mb-0">Current notices for your role will appear here.</p></div></div>
@endforelse
</div>
@if ($notices->hasPages())<div class="mt-4">{{ $notices->links() }}</div>@endif
@endsection
