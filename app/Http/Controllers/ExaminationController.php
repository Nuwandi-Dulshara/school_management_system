<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExaminationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate($this->filterRules());

        $exams = $this->queryVisibleTo($request)
            ->with(['examType', 'schoolClass', 'schoolSection'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('exam_name', 'like', "%{$search}%"))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_class_id', $id))
            ->when($filters['exam_type_id'] ?? null, fn (Builder $query, int $id) => $query->where('exam_type_id', $id))
            ->when($filters['academic_year'] ?? null, fn (Builder $query, string $year) => $query->where('academic_year', $year))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest('start_date')
            ->paginate(15)
            ->withQueryString();

        return view('examinations.index', $this->pageData($request) + compact('exams'));
    }

    public function create(Request $request): View
    {
        return view('examinations.create', $this->pageData($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->examRules());
        $this->validateSection($validated);
        Exam::create($validated);

        return redirect()->route('examinations.index')->with('success', 'Exam created successfully.');
    }

    public function edit(Request $request, Exam $exam): View
    {
        return view('examinations.edit', $this->pageData($request) + compact('exam'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate($this->examRules());
        $this->validateSection($validated);
        $exam->update($validated);

        return redirect()->route('examinations.index')->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()->route('examinations.index')->with('success', 'Exam deleted successfully.');
    }

    public function classExams(Request $request): View
    {
        $filters = $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'academic_year' => ['nullable', 'string', 'max:20'],
        ]);

        $exams = $this->queryVisibleTo($request)
            ->with(['examType', 'schoolClass', 'schoolSection'])
            ->withCount('schedules')
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_class_id', $id))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_section_id', $id))
            ->when($filters['academic_year'] ?? null, fn (Builder $query, string $year) => $query->where('academic_year', $year))
            ->orderBy('school_class_id')
            ->orderBy('school_section_id')
            ->orderByDesc('start_date')
            ->paginate(15)
            ->withQueryString();

        return view('examinations.class_exams', $this->pageData($request) + compact('exams'));
    }

    public function upcoming(Request $request): View
    {
        $exams = $this->queryVisibleTo($request)
            ->with(['examType', 'schoolClass', 'schoolSection'])
            ->upcoming()
            ->orderBy('start_date')
            ->paginate(15);

        return view('examinations.upcoming', $this->pageData($request) + compact('exams'));
    }

    public function queryVisibleTo(Request $request): Builder
    {
        $query = Exam::query();
        $user = $request->user();

        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return $query;
        }

        $query->where('status', 'published');

        if ($user->role === 'student') {
            $student = Student::query()->where('email', $user->email)->first();

            return $student
                ? $query->where('school_class_id', $student->school_class_id)
                    ->where('school_section_id', $student->school_section_id)
                : $query->whereRaw('1 = 0');
        }

        if ($user->role === 'teacher') {
            $teacher = Teacher::query()->where('email', $user->email)->first();
            $classIds = $teacher?->teachingAssignments()
                ->where('status', 'active')
                ->pluck('school_class_id')
                ->unique()
                ->all() ?? [];

            return $query->whereIn('school_class_id', $classIds);
        }

        return $query->whereRaw('1 = 0');
    }

    private function pageData(Request $request): array
    {
        return [
            'classes' => SchoolClass::query()->with('sections')->orderBy('name')->get(),
            'examTypes' => ExamType::query()->orderBy('name')->get(),
            'academicYears' => $this->academicYears(),
            'statuses' => Exam::STATUSES,
            'isManager' => in_array($request->user()->role, ['super_admin', 'admin'], true),
        ];
    }

    private function academicYears(): Collection
    {
        $year = (int) now()->format('Y');

        return Exam::query()->distinct()->pluck('academic_year')
            ->merge(collect(range($year - 1, $year + 2))->map(fn (int $start) => $start.'/'.($start + 1)))
            ->unique()
            ->sort()
            ->values();
    }

    private function filterRules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'exam_type_id' => ['nullable', 'integer', 'exists:exam_types,id'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', Rule::in(array_keys(Exam::STATUSES))],
        ];
    }

    private function examRules(): array
    {
        return [
            'exam_name' => ['required', 'string', 'max:255'],
            'exam_type_id' => ['required', 'integer', 'exists:exam_types,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'school_section_id' => ['required', 'integer', 'exists:school_sections,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(Exam::STATUSES))],
        ];
    }

    private function validateSection(array $validated): void
    {
        $valid = SchoolSection::query()
            ->whereKey($validated['school_section_id'])
            ->where('school_class_id', $validated['school_class_id'])
            ->exists();

        if (! $valid) {
            throw ValidationException::withMessages([
                'school_section_id' => 'The selected section does not belong to the selected class.',
            ]);
        }
    }
}
