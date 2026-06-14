<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubjectManagementController extends Controller
{
    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
            'manage' => ['nullable', Rule::in(['edit'])],
        ]);

        $subjects = Subject::query()
            ->withCount('schoolClasses')
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('super_admin.subjects.index', [
            'subjects' => $subjects,
            'statuses' => self::STATUSES,
            'manage' => $validated['manage'] ?? null,
        ]);
    }

    public function create(): View
    {
        return view('super_admin.subjects.create', ['statuses' => self::STATUSES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $subject = Subject::create($request->validate($this->subjectRules()));

        return redirect()
            ->route('super_admin.subjects.show', $subject)
            ->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject): View
    {
        $subject->load([
            'classAssignments.schoolClass' => fn ($query) => $query->orderBy('name'),
        ]);

        return view('super_admin.subjects.show', [
            'subject' => $subject,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Subject $subject): View
    {
        return view('super_admin.subjects.edit', [
            'subject' => $subject,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validate($this->subjectRules($subject)));

        return redirect()
            ->route('super_admin.subjects.show', $subject)
            ->with('success', 'Subject details updated successfully.');
    }

    public function toggleStatus(Subject $subject): RedirectResponse
    {
        $subject->update([
            'status' => $subject->status === 'active' ? 'inactive' : 'active',
        ]);

        if ($subject->status === 'inactive') {
            $subject->classAssignments()->update(['status' => 'inactive']);
            $subject->teacherAssignments()->update(['status' => 'inactive']);
        }

        return back()->with('success', "Subject marked as {$subject->status}.");
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()
            ->route('super_admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    public function classSubjects(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
            'edit' => ['nullable', 'integer', 'exists:class_subjects,id'],
        ]);

        $assignments = ClassSubject::query()
            ->with(['schoolClass', 'subject'])
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->whereHas('subject', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($validated['class_id'] ?? null, fn ($query, int $classId) => $query->where('school_class_id', $classId))
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->get()
            ->sortBy(fn (ClassSubject $assignment) => $assignment->schoolClass->name.' '.$assignment->subject->name)
            ->groupBy('school_class_id');

        return view('super_admin.subjects.class_subjects', [
            'assignmentsByClass' => $assignments,
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'editAssignment' => isset($validated['edit']) ? ClassSubject::findOrFail($validated['edit']) : null,
            'statuses' => self::STATUSES,
        ]);
    }

    public function storeClassSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->classSubjectRules());
        $this->ensureActiveAssignmentIsAllowed($validated);

        ClassSubject::create($validated);

        return back()->with('success', 'Subject assigned to class successfully.');
    }

    public function updateClassSubject(Request $request, ClassSubject $classSubject): RedirectResponse
    {
        $validated = $request->validate($this->classSubjectRules($classSubject));
        $this->ensureActiveAssignmentIsAllowed($validated);
        $oldClassId = $classSubject->school_class_id;
        $oldSubjectId = $classSubject->subject_id;
        $classSubject->update($validated);

        if ($oldClassId !== (int) $validated['school_class_id']
            || $oldSubjectId !== (int) $validated['subject_id']
            || $validated['status'] === 'inactive') {
            TeacherSubjectAssignment::query()
                ->where('school_class_id', $oldClassId)
                ->where('subject_id', $oldSubjectId)
                ->update(['status' => 'inactive']);
        }

        if ($validated['status'] === 'inactive') {
            TeacherSubjectAssignment::query()
                ->where('school_class_id', $validated['school_class_id'])
                ->where('subject_id', $validated['subject_id'])
                ->update(['status' => 'inactive']);
        }

        return redirect()
            ->route('super_admin.subjects.classes')
            ->with('success', 'Class subject assignment updated successfully.');
    }

    public function destroyClassSubject(ClassSubject $classSubject): RedirectResponse
    {
        TeacherSubjectAssignment::query()
            ->where('school_class_id', $classSubject->school_class_id)
            ->where('subject_id', $classSubject->subject_id)
            ->update(['status' => 'inactive']);

        $classSubject->delete();

        return back()->with('success', 'Subject removed from class successfully.');
    }

    private function subjectRules(?Subject $subject = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('subjects')->ignore($subject)],
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects')->ignore($subject)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    private function classSubjectRules(?ClassSubject $classSubject = null): array
    {
        return [
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
                Rule::unique('class_subjects')
                    ->where(fn ($query) => $query->where('school_class_id', request('school_class_id')))
                    ->ignore($classSubject),
            ],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    private function ensureActiveAssignmentIsAllowed(array $validated): void
    {
        if ($validated['status'] !== 'active') {
            return;
        }

        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $subject = Subject::findOrFail($validated['subject_id']);

        if ($schoolClass->status !== 'active' || $subject->status !== 'active') {
            throw ValidationException::withMessages([
                'status' => 'Only active classes and active subjects can have an active assignment.',
            ]);
        }
    }
}
