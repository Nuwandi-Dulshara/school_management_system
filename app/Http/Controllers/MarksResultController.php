<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ResultCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarksResultController extends Controller
{
    public function __construct(private readonly ResultCalculator $calculator) {}

    public function entry(Request $request): View
    {
        $filters = $request->validate($this->selectionRules());
        $students = collect();
        $existingMarks = collect();

        if ($this->hasCompleteSelection($filters)) {
            $exam = $this->validateContext($request, $filters);
            $students = $this->studentsForExam($exam)->get();
            $existingMarks = Mark::query()
                ->where('exam_id', $exam->id)
                ->where('subject_id', $filters['subject_id'])
                ->get()
                ->keyBy('student_id');
        }

        return view('marks.entry', $this->pageData($request) + [
            'students' => $students,
            'existingMarks' => $existingMarks,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            ...$this->requiredSelectionRules(),
            'status' => ['required', Rule::in(array_keys(Mark::STATUSES))],
            'marks' => ['required', 'array', 'min:1'],
            'marks.*.student_id' => ['required', 'integer', 'distinct', 'exists:students,id'],
            'marks.*.marks_obtained' => ['required', 'numeric', 'min:0'],
            'marks.*.maximum_marks' => ['required', 'numeric', 'gt:0'],
            'marks.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $exam = $this->validateContext($request, $validated);
        $eligibleStudentIds = $this->studentsForExam($exam)->pluck('id');
        $submittedStudentIds = collect($validated['marks'])->pluck('student_id')->map(fn ($id) => (int) $id);

        if ($submittedStudentIds->diff($eligibleStudentIds)->isNotEmpty()) {
            throw ValidationException::withMessages(['marks' => 'One or more students do not belong to the selected exam class and section.']);
        }

        foreach ($validated['marks'] as $index => $row) {
            if ((float) $row['marks_obtained'] > (float) $row['maximum_marks']) {
                throw ValidationException::withMessages([
                    "marks.{$index}.marks_obtained" => 'Marks obtained cannot exceed maximum marks.',
                ]);
            }
        }

        DB::transaction(function () use ($validated, $exam) {
            foreach ($validated['marks'] as $row) {
                Mark::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'subject_id' => $validated['subject_id'],
                        'student_id' => $row['student_id'],
                    ],
                    [
                        'academic_year' => $exam->academic_year,
                        'school_class_id' => $exam->school_class_id,
                        'school_section_id' => $exam->school_section_id,
                        'marks_obtained' => $row['marks_obtained'],
                        'maximum_marks' => $row['maximum_marks'],
                        'grade' => Mark::gradeFor((float) $row['marks_obtained'], (float) $row['maximum_marks']),
                        'remarks' => $row['remarks'] ?? null,
                        'status' => $validated['status'],
                    ]
                );
            }
        });

        $submittedStudentIds->each(fn (int $studentId) => $this->calculator->refresh($exam->id, $studentId));

        return redirect()->route('marks.index', [
            'exam_id' => $exam->id,
            'subject_id' => $validated['subject_id'],
        ])->with('success', 'Student marks saved successfully.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'status' => ['nullable', Rule::in(array_keys(Mark::STATUSES))],
        ]);

        $marks = $this->manageableMarks($request)
            ->with(['exam', 'schoolClass', 'schoolSection', 'subject', 'student'])
            ->when($filters['exam_id'] ?? null, fn (Builder $query, int $id) => $query->where('exam_id', $id))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_class_id', $id))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_section_id', $id))
            ->when($filters['subject_id'] ?? null, fn (Builder $query, int $id) => $query->where('subject_id', $id))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int $id) => $query->where('student_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('marks.index', $this->pageData($request) + compact('marks'));
    }

    public function edit(Request $request, Mark $mark): View
    {
        $this->authorizeMark($request, $mark);
        $mark->load(['exam', 'schoolClass', 'schoolSection', 'subject', 'student']);

        return view('marks.edit', [
            'mark' => $mark,
            'statuses' => Mark::STATUSES,
        ]);
    }

    public function update(Request $request, Mark $mark): RedirectResponse
    {
        $this->authorizeMark($request, $mark);
        $validated = $request->validate([
            'marks_obtained' => ['required', 'numeric', 'min:0'],
            'maximum_marks' => ['required', 'numeric', 'gt:0'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(array_keys(Mark::STATUSES))],
        ]);

        if ((float) $validated['marks_obtained'] > (float) $validated['maximum_marks']) {
            throw ValidationException::withMessages(['marks_obtained' => 'Marks obtained cannot exceed maximum marks.']);
        }

        $mark->update($validated + [
            'grade' => Mark::gradeFor((float) $validated['marks_obtained'], (float) $validated['maximum_marks']),
        ]);
        $this->calculator->refresh($mark->exam_id, $mark->student_id);

        return redirect()->route('marks.index')->with('success', 'Mark updated successfully.');
    }

    public function destroy(Request $request, Mark $mark): RedirectResponse
    {
        $this->authorizeMark($request, $mark);
        $examId = $mark->exam_id;
        $studentId = $mark->student_id;
        $mark->delete();
        $this->calculator->refresh($examId, $studentId);

        return back()->with('success', 'Mark deleted successfully.');
    }

    public function resultSheet(Request $request): View
    {
        $filters = $request->validate([
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);

        $marks = collect();
        $result = null;
        $student = null;

        if (! empty($filters['exam_id']) && ! empty($filters['student_id'])) {
            $exam = $this->manageableExams($request)->findOrFail($filters['exam_id']);
            $student = $this->studentsForExam($exam)->findOrFail($filters['student_id']);
            $marks = Mark::with('subject')->where('exam_id', $exam->id)->where('student_id', $student->id)->orderBy('subject_id')->get();
            $result = Result::where('exam_id', $exam->id)->where('student_id', $student->id)->first();
        }

        return view('marks.result_sheet', $this->pageData($request) + compact('filters', 'marks', 'result', 'student'));
    }

    public function studentResults(Request $request): View
    {
        abort_unless($request->user()->role === 'student', 403);
        $student = Student::query()->where('email', $request->user()->email)->first();
        $results = collect();

        if ($student) {
            $results = Result::query()
                ->with(['exam.examType'])
                ->where('student_id', $student->id)
                ->where('status', 'published')
                ->latest('published_at')
                ->get()
                ->map(function (Result $result) use ($student) {
                    $result->setRelation('publishedMarks', Mark::query()
                        ->with('subject')
                        ->where('exam_id', $result->exam_id)
                        ->where('student_id', $student->id)
                        ->where('status', 'published')
                        ->orderBy('subject_id')
                        ->get());

                    return $result;
                });
        }

        return view('marks.student_results', compact('student', 'results'));
    }

    public function classReport(Request $request): View
    {
        $filters = $request->validate([
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'sort' => ['nullable', Rule::in(['rank', 'total'])],
        ]);

        $results = collect();
        if (! empty($filters['exam_id'])) {
            $exam = $this->manageableExams($request)->findOrFail($filters['exam_id']);
            $results = Result::query()
                ->with('student')
                ->where('exam_id', $exam->id)
                ->when($filters['class_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_class_id', $id))
                ->when($filters['section_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_section_id', $id))
                ->when($filters['academic_year'] ?? null, fn (Builder $query, string $year) => $query->where('academic_year', $year))
                ->orderBy(($filters['sort'] ?? 'rank') === 'total' ? 'total_marks' : 'rank', ($filters['sort'] ?? 'rank') === 'total' ? 'desc' : 'asc')
                ->get();
        }

        return view('marks.class_report', $this->pageData($request) + compact('filters', 'results'));
    }

    public function subjectReport(Request $request): View
    {
        $filters = $request->validate([
            ...$this->selectionRules(),
            'academic_year' => ['nullable', 'string', 'max:20'],
        ]);

        $marks = collect();
        $summary = ['pass' => 0, 'fail' => 0];

        if ($this->hasCompleteSelection($filters)) {
            $exam = $this->validateContext($request, $filters);
            $marks = Mark::query()
                ->with('student')
                ->where('exam_id', $exam->id)
                ->where('subject_id', $filters['subject_id'])
                ->when($filters['academic_year'] ?? null, fn (Builder $query, string $year) => $query->where('academic_year', $year))
                ->orderByDesc('marks_obtained')
                ->get();
            $summary = [
                'pass' => $marks->where('grade', '!=', 'F')->count(),
                'fail' => $marks->where('grade', 'F')->count(),
            ];
        }

        return view('marks.subject_report', $this->pageData($request) + compact('filters', 'marks', 'summary'));
    }

    private function pageData(Request $request): array
    {
        $classIds = $this->teacherAssignmentPairs($request)->pluck('school_class_id')->unique();
        $subjectIds = $this->teacherAssignmentPairs($request)->pluck('subject_id')->unique();
        $isTeacher = $request->user()->role === 'teacher';

        return [
            'exams' => $this->manageableExams($request)->with(['schoolClass', 'schoolSection'])->orderByDesc('start_date')->get(),
            'classes' => SchoolClass::query()->with('sections')
                ->when($isTeacher, fn (Builder $query) => $query->whereIn('id', $classIds))
                ->orderBy('name')->get(),
            'subjects' => Subject::query()
                ->where('status', 'active')
                ->when($isTeacher, fn (Builder $query) => $query->whereIn('id', $subjectIds))
                ->orderBy('name')->get(),
            'students' => Student::query()
                ->where('status', 'active')
                ->when($isTeacher, fn (Builder $query) => $query->whereIn('school_class_id', $classIds))
                ->orderBy('full_name')->get(),
            'academicYears' => Exam::query()->distinct()->orderBy('academic_year')->pluck('academic_year'),
            'statuses' => Mark::STATUSES,
        ];
    }

    private function manageableExams(Request $request): Builder
    {
        if ($request->user()->role !== 'teacher') {
            return Exam::query();
        }

        return Exam::query()->whereIn('school_class_id', $this->teacherAssignmentPairs($request)->pluck('school_class_id')->unique());
    }

    private function manageableMarks(Request $request): Builder
    {
        $query = Mark::query();

        if ($request->user()->role !== 'teacher') {
            return $query;
        }

        $pairs = $this->teacherAssignmentPairs($request);
        if ($pairs->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($pairs) {
            foreach ($pairs as $pair) {
                $query->orWhere(function (Builder $query) use ($pair) {
                    $query->where('school_class_id', $pair->school_class_id)
                        ->where('subject_id', $pair->subject_id);
                });
            }
        });
    }

    private function teacherAssignmentPairs(Request $request): Collection
    {
        if ($request->user()->role !== 'teacher') {
            return collect();
        }

        $teacher = Teacher::query()->where('email', $request->user()->email)->first();

        return $teacher?->teachingAssignments()->where('status', 'active')->get(['school_class_id', 'subject_id']) ?? collect();
    }

    private function authorizeMark(Request $request, Mark $mark): void
    {
        abort_unless($this->manageableMarks($request)->whereKey($mark->id)->exists(), 403);
    }

    private function validateContext(Request $request, array $data): Exam
    {
        $exam = $this->manageableExams($request)->findOrFail($data['exam_id']);

        if ($exam->school_class_id !== (int) $data['class_id'] || $exam->school_section_id !== (int) $data['section_id']) {
            throw ValidationException::withMessages(['exam_id' => 'The selected exam does not belong to the selected class and section.']);
        }

        if ($request->user()->role === 'teacher') {
            $allowed = $this->teacherAssignmentPairs($request)->contains(
                fn ($pair) => $pair->school_class_id === (int) $data['class_id'] && $pair->subject_id === (int) $data['subject_id']
            );
            abort_unless($allowed, 403);
        }

        return $exam;
    }

    private function studentsForExam(Exam $exam): Builder
    {
        return Student::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($exam) {
                $query->where(function (Builder $query) use ($exam) {
                    $query->where('school_class_id', $exam->school_class_id)
                        ->where('school_section_id', $exam->school_section_id);
                })->orWhereHas('classAssignments', function (Builder $query) use ($exam) {
                    $query->where('school_class_id', $exam->school_class_id)
                        ->where('school_section_id', $exam->school_section_id)
                        ->where('academic_year', $exam->academic_year)
                        ->where('status', 'assigned');
                });
            })
            ->orderBy('full_name');
    }

    private function selectionRules(): array
    {
        return [
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
        ];
    }

    private function requiredSelectionRules(): array
    {
        return [
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['required', 'integer', 'exists:school_sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ];
    }

    private function hasCompleteSelection(array $filters): bool
    {
        return ! empty($filters['exam_id'])
            && ! empty($filters['class_id'])
            && ! empty($filters['section_id'])
            && ! empty($filters['subject_id']);
    }
}
