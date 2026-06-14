<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAssignedClass;
use App\Models\TeacherAssignedSubject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherManagementController extends Controller
{
    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    private const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
            'manage' => ['nullable', Rule::in(['classes', 'subjects'])],
        ]);

        $teachers = Teacher::query()
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('main_subject', 'like', "%{$search}%");
                });
            })
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('super_admin.teachers.index', [
            'teachers' => $teachers,
            'statuses' => self::STATUSES,
            'manage' => $validated['manage'] ?? null,
        ]);
    }

    public function create(): View
    {
        return view('super_admin.teachers.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = Teacher::create($request->validate($this->teacherRules()));

        return redirect()
            ->route('super_admin.teachers.show', $teacher)
            ->with('success', 'Teacher added successfully.');
    }

    public function show(Teacher $teacher): View
    {
        $teacher->loadCount(['assignedClasses', 'assignedSubjects']);

        return view('super_admin.teachers.show', [
            'teacher' => $teacher,
            'statuses' => self::STATUSES,
            'genders' => self::GENDERS,
        ]);
    }

    public function edit(Teacher $teacher): View
    {
        return view('super_admin.teachers.edit', array_merge(
            $this->formOptions(),
            ['teacher' => $teacher],
        ));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $teacher->update($request->validate($this->teacherRules($teacher)));

        return redirect()
            ->route('super_admin.teachers.show', $teacher)
            ->with('success', 'Teacher profile updated successfully.');
    }

    public function toggleStatus(Teacher $teacher): RedirectResponse
    {
        $teacher->update([
            'status' => $teacher->status === 'active' ? 'inactive' : 'active',
        ]);

        if ($teacher->status === 'inactive') {
            $teacher->teachingAssignments()->update(['status' => 'inactive']);
        }

        return back()->with('success', "Teacher profile marked as {$teacher->status}.");
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->delete();

        return redirect()
            ->route('super_admin.teachers.index')
            ->with('success', 'Teacher record deleted successfully.');
    }

    public function assignedClasses(Request $request, Teacher $teacher): View
    {
        $editAssignment = null;

        if ($request->filled('edit')) {
            $editAssignment = $teacher->assignedClasses()->findOrFail($request->integer('edit'));
        }

        return view('super_admin.teachers.assigned_classes', [
            'teacher' => $teacher,
            'assignments' => $teacher->assignedClasses()->latest()->get(),
            'editAssignment' => $editAssignment,
            'statuses' => self::STATUSES,
        ]);
    }

    public function storeAssignedClass(Request $request, Teacher $teacher): RedirectResponse
    {
        $teacher->assignedClasses()->create($request->validate($this->classAssignmentRules()));

        return back()->with('success', 'Class assignment added successfully.');
    }

    public function updateAssignedClass(
        Request $request,
        Teacher $teacher,
        TeacherAssignedClass $classAssignment,
    ): RedirectResponse {
        $this->ensureClassBelongsToTeacher($teacher, $classAssignment);
        $classAssignment->update($request->validate($this->classAssignmentRules()));

        return redirect()
            ->route('super_admin.teachers.classes', $teacher)
            ->with('success', 'Class assignment updated successfully.');
    }

    public function destroyAssignedClass(
        Teacher $teacher,
        TeacherAssignedClass $classAssignment,
    ): RedirectResponse {
        $this->ensureClassBelongsToTeacher($teacher, $classAssignment);
        $classAssignment->delete();

        return back()->with('success', 'Class assignment removed successfully.');
    }

    public function assignedSubjects(Request $request, Teacher $teacher): View
    {
        $editAssignment = null;

        if ($request->filled('edit')) {
            $editAssignment = $teacher->assignedSubjects()->findOrFail($request->integer('edit'));
        }

        return view('super_admin.teachers.assigned_subjects', [
            'teacher' => $teacher,
            'assignments' => $teacher->assignedSubjects()->latest()->get(),
            'editAssignment' => $editAssignment,
            'statuses' => self::STATUSES,
        ]);
    }

    public function storeAssignedSubject(Request $request, Teacher $teacher): RedirectResponse
    {
        $teacher->assignedSubjects()->create($request->validate($this->subjectAssignmentRules()));

        return back()->with('success', 'Subject assignment added successfully.');
    }

    public function updateAssignedSubject(
        Request $request,
        Teacher $teacher,
        TeacherAssignedSubject $subjectAssignment,
    ): RedirectResponse {
        $this->ensureSubjectBelongsToTeacher($teacher, $subjectAssignment);
        $subjectAssignment->update($request->validate($this->subjectAssignmentRules()));

        return redirect()
            ->route('super_admin.teachers.subjects', $teacher)
            ->with('success', 'Subject assignment updated successfully.');
    }

    public function destroyAssignedSubject(
        Teacher $teacher,
        TeacherAssignedSubject $subjectAssignment,
    ): RedirectResponse {
        $this->ensureSubjectBelongsToTeacher($teacher, $subjectAssignment);
        $subjectAssignment->delete();

        return back()->with('success', 'Subject assignment removed successfully.');
    }

    private function teacherRules(?Teacher $teacher = null): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'employee_number' => ['required', 'string', 'max:100', Rule::unique('teachers')->ignore($teacher)],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(array_keys(self::GENDERS))],
            'address' => ['required', 'string', 'max:1000'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('teachers')->ignore($teacher)],
            'qualification' => ['required', 'string', 'max:255'],
            'experience' => ['required', 'integer', 'min:0', 'max:60'],
            'main_subject' => ['required', 'string', 'max:255'],
            'joining_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    private function classAssignmentRules(): array
    {
        return [
            'class_name' => ['required', 'string', 'max:100'],
            'section' => ['required', 'string', 'max:50'],
            'academic_year' => ['required', 'string', 'max:20'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    private function subjectAssignmentRules(): array
    {
        return array_merge([
            'subject_name' => ['required', 'string', 'max:255'],
        ], $this->classAssignmentRules());
    }

    private function ensureClassBelongsToTeacher(
        Teacher $teacher,
        TeacherAssignedClass $classAssignment,
    ): void {
        abort_unless($classAssignment->teacher_id === $teacher->id, 404);
    }

    private function ensureSubjectBelongsToTeacher(
        Teacher $teacher,
        TeacherAssignedSubject $subjectAssignment,
    ): void {
        abort_unless($subjectAssignment->teacher_id === $teacher->id, 404);
    }

    private function formOptions(): array
    {
        return [
            'statuses' => self::STATUSES,
            'genders' => self::GENDERS,
        ];
    }
}
