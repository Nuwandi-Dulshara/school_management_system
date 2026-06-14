<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Services\NoticeVisibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function __construct(private readonly NoticeVisibilityService $visibility) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'audience' => ['nullable', Rule::in(array_keys(Notice::AUDIENCES))],
            'status' => ['nullable', Rule::in(array_keys(Notice::STATUSES))],
            'date' => ['nullable', 'date'],
        ]);

        $notices = Notice::query()
            ->with(['creator', 'schoolClass', 'schoolSection'])
            ->when($validated['search'] ?? null, fn (Builder $query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($validated['audience'] ?? null, fn (Builder $query, string $audience) => $query->where('audience', $audience))
            ->when($validated['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($validated['date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('publish_date', $date))
            ->latest('publish_date')
            ->paginate(15)
            ->withQueryString();

        return view('notices.index', $this->pageData() + compact('notices'));
    }

    public function create(): View
    {
        return view('notices.create', $this->pageData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $this->validateAudienceTarget($validated);
        Notice::create($this->normalizeTarget($validated) + ['created_by' => $request->user()->id]);

        return redirect()->route('notices.index')->with('success', 'Notice created successfully.');
    }

    public function show(Request $request, Notice $notice): View
    {
        abort_unless($this->visibility->canView($request->user(), $notice), 403);
        $notice->load(['creator', 'schoolClass', 'schoolSection']);

        return view('notices.show', [
            'notice' => $notice,
            'audiences' => Notice::AUDIENCES,
            'priorities' => Notice::PRIORITIES,
            'isManager' => $this->isManager($request),
        ]);
    }

    public function edit(Notice $notice): View
    {
        return view('notices.edit', $this->pageData() + compact('notice'));
    }

    public function update(Request $request, Notice $notice): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $this->validateAudienceTarget($validated);
        $notice->update($this->normalizeTarget($validated));

        return redirect()->route('notices.index')->with('success', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return redirect()->route('notices.index')->with('success', 'Notice deleted successfully.');
    }

    public function togglePublish(Notice $notice): RedirectResponse
    {
        $notice->update(['status' => $notice->status === 'published' ? 'draft' : 'published']);

        return back()->with('success', $notice->status === 'published' ? 'Notice published successfully.' : 'Notice unpublished successfully.');
    }

    public function published(Request $request): View
    {
        $notices = $this->visibility->visiblePublishedTo($request->user())
            ->with(['creator', 'schoolClass', 'schoolSection'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'important' THEN 2 ELSE 3 END")
            ->latest('publish_date')
            ->paginate(12);

        return view('notices.published', [
            'notices' => $notices,
            'audiences' => Notice::AUDIENCES,
            'priorities' => Notice::PRIORITIES,
        ]);
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'audience' => ['required', Rule::in(array_keys(Notice::AUDIENCES))],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'school_section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'publish_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'priority' => ['required', Rule::in(array_keys(Notice::PRIORITIES))],
            'status' => ['required', Rule::in(array_keys(Notice::STATUSES))],
        ];
    }

    private function validateAudienceTarget(array $validated): void
    {
        if (in_array($validated['audience'], ['class', 'section'], true) && empty($validated['school_class_id'])) {
            throw ValidationException::withMessages(['school_class_id' => 'A class is required for the selected audience.']);
        }

        if ($validated['audience'] === 'section') {
            $valid = ! empty($validated['school_section_id'])
                && SchoolSection::query()
                    ->whereKey($validated['school_section_id'])
                    ->where('school_class_id', $validated['school_class_id'])
                    ->exists();

            if (! $valid) {
                throw ValidationException::withMessages(['school_section_id' => 'Select a section that belongs to the selected class.']);
            }
        }
    }

    private function normalizeTarget(array $validated): array
    {
        if (! in_array($validated['audience'], ['class', 'section'], true)) {
            $validated['school_class_id'] = null;
            $validated['school_section_id'] = null;
        } elseif ($validated['audience'] === 'class') {
            $validated['school_section_id'] = null;
        }

        return $validated;
    }

    private function pageData(): array
    {
        return [
            'classes' => SchoolClass::query()->with('sections')->orderBy('name')->get(),
            'audiences' => Notice::AUDIENCES,
            'priorities' => Notice::PRIORITIES,
            'statuses' => Notice::STATUSES,
        ];
    }

    private function isManager(Request $request): bool
    {
        return in_array($request->user()->role, ['super_admin', 'admin'], true);
    }
}
