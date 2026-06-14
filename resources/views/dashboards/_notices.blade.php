<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <div><h4 class="text-navy mb-1">Latest Notices</h4><p class="text-muted mb-0">Announcements relevant to your account.</p></div>
    <a href="{{ route('notices.published') }}" class="btn btn-sm btn-outline-primary">View All</a>
</div>
<div class="row g-3">
@forelse ($dashboardNotices as $notice)
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm {{ $notice->priority === 'urgent' ? 'border-start border-danger border-4' : ($notice->priority === 'important' ? 'border-start border-warning border-4' : '') }}">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-2 mb-2"><span class="status-badge priority-{{ $notice->priority }}">{{ ucfirst($notice->priority) }}</span><small class="text-muted">{{ $notice->publish_date->format('M d, Y') }}</small></div>
                <h5 class="text-navy">{{ $notice->title }}</h5>
                <p class="text-muted">{{ Str::limit($notice->message, 120) }}</p>
                <a href="{{ route('notices.show', $notice) }}" class="btn btn-sm btn-outline-primary">View More</a>
            </div>
        </div>
    </div>
@empty
    <div class="col-12"><div class="alert alert-light border mb-0">No current notices for your account.</div></div>
@endforelse
</div>
