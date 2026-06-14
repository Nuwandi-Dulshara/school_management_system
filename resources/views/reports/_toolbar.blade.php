<div class="d-flex flex-wrap gap-2 justify-content-end report-actions">
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success">
        <i class="bi bi-filetype-csv me-1"></i>Export CSV
    </a>
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>Print
    </button>
</div>
