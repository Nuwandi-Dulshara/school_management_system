<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentClassAssignmentController extends Controller
{
    private const STATUSES = [
        'assigned' => 'Assigned',
        'transferred' => 'Transferred',
        'removed' => 'Removed',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate($this->filterRules());

        $assignments = $this->filteredAssignments($validated)
            ->latest('assigned_at')
            ->paginate(15)
            ->withQueryString();

        return view('super_admin.student_assignments.index', $this->pageData() + [
            'assignments' => $assignments,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $validated = $request->validate([
            'edit' => ['nullable', 'integer', 'exists:student_class_assignments,id'],
        ]);

        return view('super_admin.student_assignments.assign', $this->pageData() + [
            'editAssignment' => isset($validated['edit'])
                ? StudentClassAssignment::with(['student', 'schoolClass', 'schoolSection'])
                    ->where('status', 'assigned')
                    ->findOrFail($validated['edit'])
                : null,
            'recentAssignments' => StudentClassAssignment::query()
                ->with(['student', 'schoolClass', 'schoolSection'])
                ->latest()
                ->limit(8)
                ->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->assignmentRules());
        $student = Student::findOrFail($validated['student_id']);
        $section = $this->validatedDestination($validated, $student);
        $this->ensureSingleAssignedRecord($student, $validated['academic_year']);
        $this->ensureUniqueCombination($validated);

        DB::transaction(function () use ($validated, $student, $section) {
            StudentClassAssignment::create($validated + [
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            if ($validated['academic_year'] === self::currentAcademicYear()) {
                $this->syncStudentCurrentAssignment($student, $section);
            }
        });

        return redirect()
            ->route('super_admin.student_assignments.index', ['academic_year' => $validated['academic_year']])
            ->with('success', 'Student assigned to the class and section successfully.');
    }

    public function show(StudentClassAssignment $assignment): View
    {
        $assignment->load(['student', 'schoolClass', 'schoolSection']);

        return view('super_admin.student_assignments.show', [
            'assignment' => $assignment,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, StudentClassAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->status === 'assigned', 404);

        $validated = $request->validate($this->assignmentRules());

        if ($assignment->student_id !== (int) $validated['student_id']) {
            throw ValidationException::withMessages([
                'student_id' => 'The student cannot be changed on an existing assignment.',
            ]);
        }

        $student = Student::findOrFail($validated['student_id']);
        $section = $this->validatedDestination($validated, $student);

        $this->ensureSingleAssignedRecord($student, $validated['academic_year'], $assignment);
        $this->ensureUniqueCombination($validated, $assignment);

        DB::transaction(function () use ($assignment, $validated, $student, $section) {
            $assignment->update($validated + [
                'status' => 'assigned',
                'assigned_at' => $assignment->assigned_at ?? now(),
                'transferred_at' => null,
                'removed_at' => null,
            ]);

            if ($validated['academic_year'] === self::currentAcademicYear()) {
                $this->syncStudentCurrentAssignment($student, $section);
            }
        });

        return redirect()
            ->route('super_admin.student_assignments.show', $assignment)
            ->with('success', 'Student assignment updated successfully.');
    }

    public function transfers(Request $request): View
    {
        $validated = $request->validate($this->filterRules() + [
            'assignment' => ['nullable', 'integer', 'exists:student_class_assignments,id'],
        ]);

        $assignments = $this->filteredAssignments($validated)
            ->where('status', 'assigned')
            ->latest('assigned_at')
            ->paginate(12)
            ->withQueryString();

        return view('super_admin.student_assignments.transfer', $this->pageData() + [
            'assignments' => $assignments,
            'selectedAssignment' => isset($validated['assignment'])
                ? StudentClassAssignment::with(['student', 'schoolClass', 'schoolSection'])
                    ->where('status', 'assigned')
                    ->findOrFail($validated['assignment'])
                : null,
        ]);
    }

    public function transfer(Request $request, StudentClassAssignment $assignment): RedirectResponse
    {
        if ($assignment->status !== 'assigned') {
            return back()->with('error', 'Only an assigned student record can be transferred.');
        }

        $validated = $request->validate([
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'school_section_id' => ['required', 'integer', 'exists:school_sections,id'],
        ]);

        $assignment->load('student');
        $student = $assignment->student;
        $section = $this->validatedDestination($validated, $student);

        if ($assignment->school_class_id === (int) $validated['school_class_id']
            && $assignment->school_section_id === (int) $validated['school_section_id']) {
            throw ValidationException::withMessages([
                'school_section_id' => 'Select a different class or section for the transfer.',
            ]);
        }

        DB::transaction(function () use ($assignment, $validated, $student, $section) {
            $assignment->update([
                'status' => 'transferred',
                'transferred_at' => now(),
                'removed_at' => null,
            ]);

            StudentClassAssignment::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'school_class_id' => $validated['school_class_id'],
                    'school_section_id' => $validated['school_section_id'],
                    'academic_year' => $assignment->academic_year,
                ],
                [
                    'status' => 'assigned',
                    'assigned_at' => now(),
                    'transferred_at' => null,
                    'removed_at' => null,
                ]
            );

            if ($assignment->academic_year === self::currentAcademicYear()) {
                $this->syncStudentCurrentAssignment($student, $section);
            }
        });

        return redirect()
            ->route('super_admin.student_assignments.transfers', ['academic_year' => $assignment->academic_year])
            ->with('success', 'Student transferred successfully.');
    }

    public function destroy(StudentClassAssignment $assignment): RedirectResponse
    {
        DB::transaction(function () use ($assignment) {
            $assignment->update([
                'status' => 'removed',
                'removed_at' => now(),
                'transferred_at' => null,
            ]);

            $student = $assignment->student;

            if ($assignment->academic_year === self::currentAcademicYear()
                && $student
                && $student->school_class_id === $assignment->school_class_id
                && $student->school_section_id === $assignment->school_section_id) {
                $student->update([
                    'school_class_id' => null,
                    'school_section_id' => null,
                    'class' => 'Unassigned',
                    'section' => 'Unassigned',
                ]);
            }
        });

        return back()->with('success', 'Student assignment removed successfully.');
    }

    public function academicYears(Request $request): View
    {
        $validated = $request->validate([
            'academic_year' => ['nullable', 'regex:/^\d{4}\/\d{4}$/'],
        ]);

        $selectedYear = $validated['academic_year'] ?? self::currentAcademicYear();
        $assignments = StudentClassAssignment::query()
            ->with(['student', 'schoolClass', 'schoolSection'])
            ->where('academic_year', $selectedYear)
            ->latest('assigned_at')
            ->paginate(15)
            ->withQueryString();

        $yearSummaries = StudentClassAssignment::query()
            ->select('academic_year')
            ->selectRaw("SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned_count")
            ->selectRaw("SUM(CASE WHEN status = 'transferred' THEN 1 ELSE 0 END) as transferred_count")
            ->selectRaw("SUM(CASE WHEN status = 'removed' THEN 1 ELSE 0 END) as removed_count")
            ->groupBy('academic_year')
            ->orderByDesc('academic_year')
            ->get();

        return view('super_admin.student_assignments.academic_years', [
            'assignments' => $assignments,
            'academicYears' => $this->academicYearsList(),
            'selectedYear' => $selectedYear,
            'yearSummaries' => $yearSummaries,
            'statuses' => self::STATUSES,
        ]);
    }

    public static function currentAcademicYear(): string
    {
        $year = (int) now()->format('Y');

        return $year.'/'.($year + 1);
    }

    private function assignmentRules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'school_section_id' => ['required', 'integer', 'exists:school_sections,id'],
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
        ];
    }

    private function filterRules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['nullable', 'regex:/^\d{4}\/\d{4}$/'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    private function filteredAssignments(array $filters): Builder
    {
        return StudentClassAssignment::query()
            ->with(['student', 'schoolClass', 'schoolSection'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->whereHas('student', function (Builder $query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $query->orWhereKey((int) $search);
                    }
                });
            })
            ->when($filters['academic_year'] ?? null, fn (Builder $query, string $year) => $query->where('academic_year', $year))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $classId) => $query->where('school_class_id', $classId))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int $sectionId) => $query->where('school_section_id', $sectionId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    private function validatedDestination(array $validated, Student $student): SchoolSection
    {
        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $section = SchoolSection::query()
            ->whereKey($validated['school_section_id'])
            ->where('school_class_id', $schoolClass->id)
            ->first();

        if (! $section) {
            throw ValidationException::withMessages([
                'school_section_id' => 'The selected section does not belong to the selected class.',
            ]);
        }

        if ($schoolClass->status !== 'active' || $section->status !== 'active') {
            throw ValidationException::withMessages([
                'school_class_id' => 'Students can only be assigned to active classes and sections.',
            ]);
        }

        if ($student->school_class_id !== $schoolClass->id
            && $schoolClass->students()->count() >= $schoolClass->capacity) {
            throw ValidationException::withMessages([
                'school_class_id' => 'The selected class has reached its capacity.',
            ]);
        }

        if ($student->school_section_id !== $section->id
            && $section->students()->count() >= $section->capacity) {
            throw ValidationException::withMessages([
                'school_section_id' => 'The selected section has reached its capacity.',
            ]);
        }

        return $section->load('schoolClass');
    }

    private function ensureSingleAssignedRecord(
        Student $student,
        string $academicYear,
        ?StudentClassAssignment $ignore = null
    ): void {
        $exists = StudentClassAssignment::query()
            ->where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->where('status', 'assigned')
            ->when($ignore, fn (Builder $query) => $query->where('id', '!=', $ignore->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'student_id' => 'This student already has an assigned class and section for the selected academic year.',
            ]);
        }
    }

    private function ensureUniqueCombination(
        array $validated,
        ?StudentClassAssignment $ignore = null
    ): void {
        $exists = StudentClassAssignment::query()
            ->where('student_id', $validated['student_id'])
            ->where('school_class_id', $validated['school_class_id'])
            ->where('school_section_id', $validated['school_section_id'])
            ->where('academic_year', $validated['academic_year'])
            ->when($ignore, fn (Builder $query) => $query->where('id', '!=', $ignore->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'student_id' => 'This exact student, class, section, and academic year assignment already exists.',
            ]);
        }
    }

    private function syncStudentCurrentAssignment(Student $student, SchoolSection $section): void
    {
        $student->update([
            'school_class_id' => $section->school_class_id,
            'school_section_id' => $section->id,
            'class' => $section->schoolClass->name,
            'section' => $section->name,
        ]);
    }

    private function pageData(): array
    {
        return [
            'academicYears' => $this->academicYearsList(),
            'classes' => SchoolClass::query()->with('sections')->orderBy('name')->get(),
            'students' => Student::query()->orderBy('full_name')->get(),
        ];
    }

    private function academicYearsList(): array
    {
        $currentYear = (int) now()->format('Y');
        $years = collect(range($currentYear - 3, $currentYear + 2))
            ->map(fn (int $year) => $year.'/'.($year + 1));

        $storedYears = StudentClassAssignment::query()
            ->distinct()
            ->pluck('academic_year');

        return $years
            ->merge($storedYears)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }
}
