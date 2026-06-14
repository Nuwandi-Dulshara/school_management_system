@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-3">
    <div class="col-md-8"><label class="form-label fw-semibold">Notice Title</label><input type="text" name="title" class="form-control" value="{{ old('title', $notice->title ?? '') }}" required></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Audience</label><select name="audience" id="notice_audience" class="form-select" required>@foreach ($audiences as $value => $label)<option value="{{ $value }}" @selected(old('audience', $notice->audience ?? 'all_users') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-6 notice-target notice-class-target"><label class="form-label fw-semibold">Class</label><select name="school_class_id" id="notice_class_id" class="form-select"><option value="">Select class</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected((string) old('school_class_id', $notice->school_class_id ?? '') === (string) $class->id)>{{ $class->name }}</option>@endforeach</select></div>
    <div class="col-md-6 notice-target notice-section-target"><label class="form-label fw-semibold">Section</label><select name="school_section_id" id="notice_section_id" class="form-select"><option value="">Select section</option>@foreach ($classes as $class)@foreach ($class->sections as $section)<option value="{{ $section->id }}" data-class-id="{{ $class->id }}" @selected((string) old('school_section_id', $notice->school_section_id ?? '') === (string) $section->id)>{{ $class->name }} - {{ $section->name }}</option>@endforeach @endforeach</select></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Publish Date</label><input type="date" name="publish_date" class="form-control" value="{{ old('publish_date', isset($notice) ? $notice->publish_date->toDateString() : now()->toDateString()) }}" required></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Expiry Date</label><input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', isset($notice) ? $notice->expiry_date?->toDateString() : '') }}"></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Priority</label><select name="priority" class="form-select">@foreach ($priorities as $value => $label)<option value="{{ $value }}" @selected(old('priority', $notice->priority ?? 'normal') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select">@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $notice->status ?? 'draft') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label fw-semibold">Notice Description / Message</label><textarea name="message" rows="8" class="form-control" required>{{ old('message', $notice->message ?? '') }}</textarea></div>
</div>
<div class="d-flex gap-2 mt-4"><button class="btn btn-academic">{{ $submitLabel }}</button><a href="{{ route('notices.index') }}" class="btn btn-outline-secondary">Cancel</a></div>

@push('notice_scripts')
<script>
    const noticeAudience = document.getElementById('notice_audience');
    const noticeClass = document.getElementById('notice_class_id');
    const noticeSection = document.getElementById('notice_section_id');
    const updateNoticeTargets = () => {
        const audience = noticeAudience.value;
        document.querySelector('.notice-class-target').hidden = !['class', 'section'].includes(audience);
        document.querySelector('.notice-section-target').hidden = audience !== 'section';
        [...noticeSection.options].forEach((option, index) => option.hidden = index > 0 && noticeClass.value && option.dataset.classId !== noticeClass.value);
    };
    noticeAudience.addEventListener('change', updateNoticeTargets);
    noticeClass.addEventListener('change', () => { noticeSection.value = ''; updateNoticeTargets(); });
    updateNoticeTargets();
</script>
@endpush
