<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClassSectionManagementController extends Controller
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
            'manage' => ['nullable', Rule::in(['students'])],
        ]);

        $classes = SchoolClass::query()
            ->withCount(['sections', 'students'])
            ->when($validated['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('super_admin.classes.index', [
            'classes' => $classes,
            'statuses' => self::STATUSES,
            'manage' => $validated['manage'] ?? null,
        ]);
    }

    public function create(): View
    {
        return view('super_admin.classes.create', ['statuses' => self::STATUSES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolClass = SchoolClass::create($request->validate($this->classRules()));

        return redirect()
            ->route('super_admin.classes.show', $schoolClass)
            ->with('success', 'Class created successfully.');
    }

    public function show(SchoolClass $schoolClass): View
    {
        $schoolClass->load([
            'sections' => fn ($query) => $query->withCount('students')->orderBy('name'),
        ])->loadCount('students');

        return view('super_admin.classes.show', [
            'schoolClass' => $schoolClass,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('super_admin.classes.edit', [
            'schoolClass' => $schoolClass,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate($this->classRules($schoolClass));

        if ($validated['capacity'] < $schoolClass->students()->count()) {
            throw ValidationException::withMessages([
                'capacity' => 'Class capacity cannot be lower than the current number of assigned students.',
            ]);
        }

        DB::transaction(function () use ($schoolClass, $validated) {
            $schoolClass->update($validated);
            $schoolClass->students()->update(['class' => $schoolClass->name]);
        });

        return redirect()
            ->route('super_admin.classes.show', $schoolClass)
            ->with('success', 'Class details updated successfully.');
    }

    public function toggleStatus(SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update([
            'status' => $schoolClass->status === 'active' ? 'inactive' : 'active',
        ]);

        if ($schoolClass->status === 'inactive') {
            $schoolClass->teacherAssignments()->update(['status' => 'inactive']);
        }

        return back()->with('success', "Class marked as {$schoolClass->status}.");
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        if ($schoolClass->sections()->exists()
            || $schoolClass->students()->exists()
            || $schoolClass->studentAssignments()->exists()) {
            return back()->with('error', 'Remove assigned students, sections, and assignment history before deleting this class.');
        }

        $schoolClass->delete();

        return redirect()
            ->route('super_admin.classes.index')
            ->with('success', 'Class deleted successfully.');
    }

    public function sections(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ]);

        $sections = SchoolSection::query()
            ->with('schoolClass')
            ->withCount('students')
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('schoolClass', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($validated['class_id'] ?? null, fn ($query, int $classId) => $query->where('school_class_id', $classId))
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('school_class_id')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('super_admin.sections.index', [
            'sections' => $sections,
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function createSection(): View
    {
        return view('super_admin.sections.create', [
            'classes' => SchoolClass::query()->where('status', 'active')->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $section = SchoolSection::create($request->validate($this->sectionRules()));

        return redirect()
            ->route('super_admin.sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function editSection(SchoolSection $schoolSection): View
    {
        return view('super_admin.sections.edit', [
            'schoolSection' => $schoolSection,
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function updateSection(Request $request, SchoolSection $schoolSection): RedirectResponse
    {
        $validated = $request->validate($this->sectionRules($schoolSection));

        if ($validated['capacity'] < $schoolSection->students()->count()) {
            throw ValidationException::withMessages([
                'capacity' => 'Section capacity cannot be lower than the current number of assigned students.',
            ]);
        }

        $newClass = SchoolClass::findOrFail($validated['school_class_id']);
        $sectionStudentCount = $schoolSection->students()->count();

        if ($newClass->id !== $schoolSection->school_class_id
            && $newClass->students()->count() + $sectionStudentCount > $newClass->capacity) {
            throw ValidationException::withMessages([
                'school_class_id' => "The selected class does not have enough capacity for this section's students.",
            ]);
        }

        DB::transaction(function () use ($schoolSection, $validated, $newClass) {
            $schoolSection->update($validated);
            $schoolSection->students()->update([
                'school_class_id' => $newClass->id,
                'class' => $newClass->name,
                'section' => $schoolSection->name,
            ]);
        });

        return redirect()
            ->route('super_admin.sections.index')
            ->with('success', 'Section details updated successfully.');
    }

    public function toggleSectionStatus(SchoolSection $schoolSection): RedirectResponse
    {
        $schoolSection->update([
            'status' => $schoolSection->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', "Section marked as {$schoolSection->status}.");
    }

    public function destroySection(SchoolSection $schoolSection): RedirectResponse
    {
        if ($schoolSection->students()->exists() || $schoolSection->studentAssignments()->exists()) {
            return back()->with('error', 'Move or remove assigned students and assignment history before deleting this section.');
        }

        $schoolSection->delete();

        return back()->with('success', 'Section deleted successfully.');
    }

    public function students(Request $request, SchoolClass $schoolClass): View
    {
        $validated = $request->validate([
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $students = $schoolClass->students()
            ->with('schoolSection')
            ->when($validated['section_id'] ?? null, fn ($query, int $sectionId) => $query->where('school_section_id', $sectionId))
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('super_admin.classes.students', [
            'schoolClass' => $schoolClass,
            'students' => $students,
            'sections' => $schoolClass->sections()->orderBy('name')->get(),
            'availableStudents' => Student::query()
                ->whereNull('school_class_id')
                ->orderBy('full_name')
                ->get(),
        ]);
    }

    public function assignStudent(Request $request, SchoolClass $schoolClass, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'school_section_id' => ['required', 'integer', 'exists:school_sections,id'],
        ]);

        $section = $schoolClass->sections()->whereKey($validated['school_section_id'])->firstOrFail();

        if ($schoolClass->status !== 'active' || $section->status !== 'active') {
            return back()->with('error', 'Students can only be assigned to active classes and sections.');
        }

        if ($student->school_class_id !== $schoolClass->id && $schoolClass->students()->count() >= $schoolClass->capacity) {
            return back()->with('error', 'This class has reached its capacity.');
        }

        if ($student->school_section_id !== $section->id && $section->students()->count() >= $section->capacity) {
            return back()->with('error', 'This section has reached its capacity.');
        }

        DB::transaction(function () use ($student, $schoolClass, $section) {
            $academicYear = StudentClassAssignmentController::currentAcademicYear();

            StudentClassAssignment::query()
                ->where('student_id', $student->id)
                ->where('academic_year', $academicYear)
                ->where('status', 'assigned')
                ->where(function ($query) use ($schoolClass, $section) {
                    $query->where('school_class_id', '!=', $schoolClass->id)
                        ->orWhere('school_section_id', '!=', $section->id);
                })
                ->update([
                    'status' => 'transferred',
                    'transferred_at' => now(),
                    'updated_at' => now(),
                ]);

            StudentClassAssignment::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'school_class_id' => $schoolClass->id,
                    'school_section_id' => $section->id,
                    'academic_year' => $academicYear,
                ],
                [
                    'status' => 'assigned',
                    'assigned_at' => now(),
                    'transferred_at' => null,
                    'removed_at' => null,
                ]
            );

            $student->update([
                'school_class_id' => $schoolClass->id,
                'school_section_id' => $section->id,
                'class' => $schoolClass->name,
                'section' => $section->name,
            ]);
        });

        return back()->with('success', 'Student class and section assignment updated successfully.');
    }

    public function removeStudent(SchoolClass $schoolClass, Student $student): RedirectResponse
    {
        abort_unless($student->school_class_id === $schoolClass->id, 404);

        DB::transaction(function () use ($schoolClass, $student) {
            StudentClassAssignment::query()
                ->where('student_id', $student->id)
                ->where('school_class_id', $schoolClass->id)
                ->where('academic_year', StudentClassAssignmentController::currentAcademicYear())
                ->where('status', 'assigned')
                ->update([
                    'status' => 'removed',
                    'removed_at' => now(),
                    'updated_at' => now(),
                ]);

            $student->update([
                'school_class_id' => null,
                'school_section_id' => null,
                'class' => 'Unassigned',
                'section' => 'Unassigned',
            ]);
        });

        return back()->with('success', 'Student removed from the class.');
    }

    private function classRules(?SchoolClass $schoolClass = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('school_classes')->ignore($schoolClass)],
            'capacity' => ['required', 'integer', 'min:1', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    private function sectionRules(?SchoolSection $schoolSection = null): array
    {
        return [
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('school_sections')
                    ->where(fn ($query) => $query->where('school_class_id', request('school_class_id')))
                    ->ignore($schoolSection),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }
}
