<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherSubjectAssignmentController extends Controller
{
    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate($this->filterRules());

        return view('super_admin.teacher_assignments.index', $this->pageData() + [
            'assignments' => $this->filteredAssignments($validated)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $validated = $request->validate([
            'edit' => ['nullable', 'integer', 'exists:teacher_subject_assignments,id'],
        ]);

        return view('super_admin.teacher_assignments.assign', $this->pageData() + [
            'editAssignment' => isset($validated['edit'])
                ? TeacherSubjectAssignment::with(['teacher', 'schoolClass', 'subject'])->findOrFail($validated['edit'])
                : null,
            'recentAssignments' => TeacherSubjectAssignment::query()
                ->with(['teacher', 'schoolClass', 'subject'])
                ->latest()
                ->limit(8)
                ->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->assignmentRules());
        $this->ensureActiveAssignmentIsAllowed($validated);

        $assignment = TeacherSubjectAssignment::create($validated);

        return redirect()
            ->route('super_admin.teacher_assignments.show', $assignment)
            ->with('success', 'Teacher assigned to the class and subject successfully.');
    }

    public function show(TeacherSubjectAssignment $assignment): View
    {
        $assignment->load(['teacher', 'schoolClass', 'subject']);

        return view('super_admin.teacher_assignments.show', [
            'assignment' => $assignment,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, TeacherSubjectAssignment $assignment): RedirectResponse
    {
        $validated = $request->validate($this->assignmentRules($assignment));
        $this->ensureActiveAssignmentIsAllowed($validated);
        $assignment->update($validated);

        return redirect()
            ->route('super_admin.teacher_assignments.show', $assignment)
            ->with('success', 'Teacher assignment updated successfully.');
    }

    public function toggleStatus(TeacherSubjectAssignment $assignment): RedirectResponse
    {
        $newStatus = $assignment->status === 'active' ? 'inactive' : 'active';
        $validated = [
            'teacher_id' => $assignment->teacher_id,
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'status' => $newStatus,
        ];

        $this->ensureActiveAssignmentIsAllowed($validated);
        $assignment->update(['status' => $newStatus]);

        return back()->with('success', "Teacher assignment marked as {$newStatus}.");
    }

    public function destroy(TeacherSubjectAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return redirect()
            ->route('super_admin.teacher_assignments.index')
            ->with('success', 'Teacher assignment removed successfully.');
    }

    public function classWise(Request $request): View
    {
        $validated = $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ]);

        $assignments = TeacherSubjectAssignment::query()
            ->with(['teacher', 'schoolClass', 'subject'])
            ->when($validated['class_id'] ?? null, fn (Builder $query, int $classId) => $query->where('school_class_id', $classId))
            ->when($validated['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->get()
            ->sortBy(fn (TeacherSubjectAssignment $assignment) => $assignment->schoolClass->name.' '.$assignment->subject->name.' '.$assignment->teacher->full_name)
            ->groupBy('school_class_id');

        return view('super_admin.teacher_assignments.class_wise', [
            'assignmentsByClass' => $assignments,
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function subjectWise(Request $request): View
    {
        $validated = $request->validate([
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ]);

        $assignments = TeacherSubjectAssignment::query()
            ->with(['teacher', 'schoolClass', 'subject'])
            ->when($validated['subject_id'] ?? null, fn (Builder $query, int $subjectId) => $query->where('subject_id', $subjectId))
            ->when($validated['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->get()
            ->sortBy(fn (TeacherSubjectAssignment $assignment) => $assignment->subject->name.' '.$assignment->schoolClass->name.' '.$assignment->teacher->full_name)
            ->groupBy('subject_id');

        return view('super_admin.teacher_assignments.subject_wise', [
            'assignmentsBySubject' => $assignments,
            'subjects' => Subject::query()->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    private function assignmentRules(?TeacherSubjectAssignment $assignment = null): array
    {
        return [
            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
                Rule::unique('teacher_subject_assignments')
                    ->where(fn ($query) => $query
                        ->where('school_class_id', request('school_class_id'))
                        ->where('subject_id', request('subject_id')))
                    ->ignore($assignment),
            ],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    private function filterRules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    private function filteredAssignments(array $filters): Builder
    {
        return TeacherSubjectAssignment::query()
            ->with(['teacher', 'schoolClass', 'subject'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('teacher', fn (Builder $query) => $query->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn (Builder $query) => $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('schoolClass', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['teacher_id'] ?? null, fn (Builder $query, int $teacherId) => $query->where('teacher_id', $teacherId))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $classId) => $query->where('school_class_id', $classId))
            ->when($filters['subject_id'] ?? null, fn (Builder $query, int $subjectId) => $query->where('subject_id', $subjectId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    private function ensureActiveAssignmentIsAllowed(array $validated): void
    {
        if ($validated['status'] !== 'active') {
            return;
        }

        $teacher = Teacher::findOrFail($validated['teacher_id']);
        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $subject = Subject::findOrFail($validated['subject_id']);
        $classSubjectIsActive = ClassSubject::query()
            ->where('school_class_id', $schoolClass->id)
            ->where('subject_id', $subject->id)
            ->where('status', 'active')
            ->exists();

        if ($teacher->status !== 'active'
            || $schoolClass->status !== 'active'
            || $subject->status !== 'active'
            || ! $classSubjectIsActive) {
            throw ValidationException::withMessages([
                'status' => 'Active assignments require an active teacher and an active subject offered by the selected active class.',
            ]);
        }
    }

    private function pageData(): array
    {
        return [
            'teachers' => Teacher::query()->orderBy('full_name')->get(),
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'classSubjects' => ClassSubject::query()
                ->with(['schoolClass', 'subject'])
                ->orderBy('school_class_id')
                ->get(),
        ];
    }
}
