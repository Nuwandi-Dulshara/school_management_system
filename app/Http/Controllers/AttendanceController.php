<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function mark(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
        ]);

        $students = collect();
        $existingCount = 0;

        if (! empty($validated['class_id']) && ! empty($validated['section_id'])) {
            $this->validateSection((int) $validated['class_id'], (int) $validated['section_id']);
            $students = $this->studentsForClassSection(
                (int) $validated['class_id'],
                (int) $validated['section_id']
            )->get();

            $existingCount = Attendance::query()
                ->whereDate('attendance_date', $validated['date'] ?? now()->toDateString())
                ->where('school_class_id', $validated['class_id'])
                ->where('school_section_id', $validated['section_id'])
                ->count();
        }

        return view('attendance.mark', $this->pageData() + [
            'students' => $students,
            'existingCount' => $existingCount,
            'selectedDate' => $validated['date'] ?? now()->toDateString(),
            'selectedClassId' => $validated['class_id'] ?? null,
            'selectedSectionId' => $validated['section_id'] ?? null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'school_section_id' => ['required', 'integer', 'exists:school_sections,id'],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*' => ['required', Rule::in(array_keys(Attendance::STATUSES))],
        ]);

        $this->validateSection($validated['school_class_id'], $validated['school_section_id']);

        $studentIds = collect(array_keys($validated['attendance']))
            ->filter(fn ($id) => ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->values();

        $eligibleIds = $this->studentsForClassSection(
            $validated['school_class_id'],
            $validated['school_section_id']
        )->pluck('id');

        if ($studentIds->count() !== $eligibleIds->count()
            || $studentIds->diff($eligibleIds)->isNotEmpty()
            || $eligibleIds->diff($studentIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance status is required for every active student in the selected class and section.',
            ]);
        }

        $duplicateStudents = Attendance::query()
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->where('school_class_id', $validated['school_class_id'])
            ->where('school_section_id', $validated['school_section_id'])
            ->whereIn('student_id', $studentIds)
            ->exists();

        if ($duplicateStudents) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance has already been marked for one or more selected students on this date. Use Edit Attendance instead.',
            ]);
        }

        DB::transaction(function () use ($validated, $studentIds, $request) {
            $now = now();
            $rows = $studentIds->map(fn (int $studentId) => [
                'student_id' => $studentId,
                'school_class_id' => $validated['school_class_id'],
                'school_section_id' => $validated['school_section_id'],
                'attendance_date' => $validated['attendance_date'],
                'status' => $validated['attendance'][$studentId],
                'recorded_by' => $request->user()->id,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            Attendance::insert($rows);
        });

        return redirect()
            ->route('attendance.index', [
                'date' => $validated['attendance_date'],
                'class_id' => $validated['school_class_id'],
                'section_id' => $validated['school_section_id'],
            ])
            ->with('success', 'Attendance saved successfully.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate($this->listFilterRules());

        $attendances = $this->filteredAttendances($filters)
            ->orderByDesc('attendance_date')
            ->orderBy('student_id')
            ->paginate(20)
            ->withQueryString();

        return view('attendance.index', $this->pageData() + [
            'attendances' => $attendances,
        ]);
    }

    public function editList(Request $request): View
    {
        $filters = $request->validate($this->listFilterRules());

        $attendances = $this->filteredAttendances($filters)
            ->orderByDesc('attendance_date')
            ->orderBy('student_id')
            ->paginate(20)
            ->withQueryString();

        return view('attendance.edit_list', $this->pageData() + [
            'attendances' => $attendances,
        ]);
    }

    public function edit(Attendance $attendance): View
    {
        $attendance->load(['student', 'schoolClass', 'schoolSection']);

        return view('attendance.edit', [
            'attendance' => $attendance,
            'statuses' => Attendance::STATUSES,
        ]);
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Attendance::STATUSES))],
        ]);

        $attendance->update($validated);

        return redirect()
            ->route('attendance.edit_list', [
                'date' => $attendance->attendance_date->toDateString(),
                'class_id' => $attendance->school_class_id,
                'section_id' => $attendance->school_section_id,
            ])
            ->with('success', 'Attendance status updated successfully.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return back()->with('success', 'Attendance entry deleted successfully.');
    }

    public function studentHistory(Request $request): View
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $students = Student::query()
            ->when($validated['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->limit(100)
            ->get();

        $selectedStudent = isset($validated['student_id'])
            ? Student::findOrFail($validated['student_id'])
            : null;

        $history = $selectedStudent
            ? Attendance::with(['schoolClass', 'schoolSection'])
                ->where('student_id', $selectedStudent->id)
                ->latest('attendance_date')
                ->paginate(25)
                ->withQueryString()
            : null;

        return view('attendance.student_history', [
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'history' => $history,
            'statuses' => Attendance::STATUSES,
        ]);
    }

    public function classReport(Request $request): View
    {
        $validated = $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $report = collect();

        if (! empty($validated['class_id'])
            && ! empty($validated['section_id'])
            && ! empty($validated['date_from'])
            && ! empty($validated['date_to'])) {
            $this->validateSection((int) $validated['class_id'], (int) $validated['section_id']);

            $report = Attendance::query()
                ->select('attendance_date')
                ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count")
                ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count")
                ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count")
                ->where('school_class_id', $validated['class_id'])
                ->where('school_section_id', $validated['section_id'])
                ->whereBetween('attendance_date', [$validated['date_from'], $validated['date_to']])
                ->groupBy('attendance_date')
                ->orderBy('attendance_date')
                ->get();
        }

        return view('attendance.class_report', $this->pageData() + [
            'report' => $report,
        ]);
    }

    public function dailyReport(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);
        $date = $validated['date'] ?? now()->toDateString();

        $report = Attendance::query()
            ->with(['schoolClass', 'schoolSection'])
            ->select('school_class_id', 'school_section_id')
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count")
            ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count")
            ->whereDate('attendance_date', $date)
            ->groupBy('school_class_id', 'school_section_id')
            ->orderBy('school_class_id')
            ->orderBy('school_section_id')
            ->get();

        return view('attendance.daily_report', [
            'date' => $date,
            'report' => $report,
        ]);
    }

    private function listFilterRules(): array
    {
        return [
            'date' => ['nullable', 'date'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
        ];
    }

    private function filteredAttendances(array $filters): Builder
    {
        return Attendance::query()
            ->with(['student', 'schoolClass', 'schoolSection'])
            ->when($filters['date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('attendance_date', $date))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $classId) => $query->where('school_class_id', $classId))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int $sectionId) => $query->where('school_section_id', $sectionId));
    }

    private function studentsForClassSection(int $classId, int $sectionId): Builder
    {
        return Student::query()
            ->where('school_class_id', $classId)
            ->where('school_section_id', $sectionId)
            ->where('status', 'active')
            ->orderBy('full_name');
    }

    private function validateSection(int $classId, int $sectionId): SchoolSection
    {
        $section = SchoolSection::query()
            ->whereKey($sectionId)
            ->where('school_class_id', $classId)
            ->first();

        if (! $section) {
            throw ValidationException::withMessages([
                'section_id' => 'The selected section does not belong to the selected class.',
            ]);
        }

        return $section;
    }

    private function pageData(): array
    {
        return [
            'classes' => SchoolClass::query()->with('sections')->orderBy('name')->get(),
            'statuses' => Attendance::STATUSES,
        ];
    }
}
