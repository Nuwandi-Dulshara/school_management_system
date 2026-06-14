<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\FeeAssignment;
use App\Models\FeeType;
use App\Models\Mark;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function students(Request $request)
    {
        $this->authorizeReport($request, ['super_admin', 'admin', 'teacher', 'student']);

        $query = Student::query()
            ->with(['guardian', 'schoolClass', 'schoolSection'])
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->string('search');
                $query->where(function (Builder $query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('class_id'), fn (Builder $query) => $query->where('school_class_id', $request->integer('class_id')))
            ->when($request->filled('section_id'), fn (Builder $query) => $query->where('school_section_id', $request->integer('section_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->status))
            ->when($request->filled('academic_year'), function (Builder $query) use ($request) {
                $query->whereHas('classAssignments', fn (Builder $assignment) => $assignment->where('academic_year', $request->academic_year));
            });

        $this->scopeStudents($query, $request);

        if ($request->export === 'csv') {
            return $this->csv('student-report.csv',
                ['Admission No.', 'Student Name', 'Class', 'Section', 'Guardian', 'Contact', 'Status'],
                $query->orderBy('full_name')->get()->map(fn (Student $student) => [
                    $student->admission_number,
                    $student->full_name,
                    $student->schoolClass?->name,
                    $student->schoolSection?->name,
                    $student->guardian?->guardian_name,
                    $student->contact_number,
                    $student->status,
                ])
            );
        }

        return view('reports.students', array_merge($this->filters($request), [
            'students' => $query->orderBy('full_name')->paginate(20)->withQueryString(),
        ]));
    }

    public function teachers(Request $request)
    {
        $this->authorizeReport($request, ['super_admin', 'admin']);

        $query = Teacher::query()
            ->with(['teachingAssignments.schoolClass', 'teachingAssignments.subject'])
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->string('search');
                $query->where(fn (Builder $query) => $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->status))
            ->when($request->filled('class_id'), fn (Builder $query) => $query->whereHas(
                'teachingAssignments',
                fn (Builder $assignment) => $assignment->where('school_class_id', $request->integer('class_id'))
            ))
            ->when($request->filled('subject_id'), fn (Builder $query) => $query->whereHas(
                'teachingAssignments',
                fn (Builder $assignment) => $assignment->where('subject_id', $request->integer('subject_id'))
            ));

        if ($request->export === 'csv') {
            return $this->csv('teacher-report.csv',
                ['Employee No.', 'Teacher Name', 'Classes', 'Subjects', 'Contact', 'Email', 'Status'],
                $query->orderBy('full_name')->get()->map(fn (Teacher $teacher) => [
                    $teacher->employee_number,
                    $teacher->full_name,
                    $teacher->teachingAssignments->pluck('schoolClass.name')->filter()->unique()->join(', '),
                    $teacher->teachingAssignments->pluck('subject.name')->filter()->unique()->join(', '),
                    $teacher->contact_number,
                    $teacher->email,
                    $teacher->status,
                ])
            );
        }

        return view('reports.teachers', array_merge($this->filters($request), [
            'teachers' => $query->orderBy('full_name')->paginate(20)->withQueryString(),
        ]));
    }

    public function attendance(Request $request)
    {
        $this->authorizeReport($request, ['super_admin', 'admin', 'teacher', 'student']);

        $base = Attendance::query()
            ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('attendance_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('attendance_date', '<=', $request->date_to))
            ->when($request->filled('class_id'), fn (Builder $query) => $query->where('school_class_id', $request->integer('class_id')))
            ->when($request->filled('section_id'), fn (Builder $query) => $query->where('school_section_id', $request->integer('section_id')))
            ->when($request->filled('student_id'), fn (Builder $query) => $query->where('student_id', $request->integer('student_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->status))
            ->when($request->filled('academic_year'), function (Builder $query) use ($request) {
                $query->whereHas('student.classAssignments', fn (Builder $assignment) => $assignment->where('academic_year', $request->academic_year));
            });

        $this->scopeClassData($base, $request, true);

        $summaryQuery = (clone $base)
            ->select('student_id', 'school_class_id', 'school_section_id')
            ->selectRaw('COUNT(*) as total_days')
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days")
            ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days")
            ->with(['student', 'schoolClass', 'schoolSection'])
            ->groupBy('student_id', 'school_class_id', 'school_section_id');

        if ($request->export === 'csv') {
            return $this->csv('attendance-report.csv',
                ['Student', 'Class', 'Section', 'Total Days', 'Present', 'Absent', 'Late', 'Attendance %'],
                $summaryQuery->get()->map(fn ($row) => [
                    $row->student?->full_name,
                    $row->schoolClass?->name,
                    $row->schoolSection?->name,
                    $row->total_days,
                    $row->present_days,
                    $row->absent_days,
                    $row->late_days,
                    $row->total_days ? round(($row->present_days / $row->total_days) * 100, 2) : 0,
                ])
            );
        }

        $dailySummary = (clone $base)
            ->select('attendance_date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent")
            ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late")
            ->groupBy('attendance_date')
            ->orderByDesc('attendance_date')
            ->limit(31)
            ->get();

        return view('reports.attendance', array_merge($this->filters($request), [
            'attendanceRows' => $summaryQuery->orderBy('student_id')->paginate(20)->withQueryString(),
            'dailySummary' => $dailySummary,
        ]));
    }

    public function exams(Request $request)
    {
        $this->authorizeReport($request, ['super_admin', 'admin', 'teacher', 'student']);

        $query = Exam::query()
            ->with(['examType', 'schoolClass', 'schoolSection'])
            ->withCount('schedules')
            ->when($request->filled('academic_year'), fn (Builder $query) => $query->where('academic_year', $request->academic_year))
            ->when($request->filled('exam_type_id'), fn (Builder $query) => $query->where('exam_type_id', $request->integer('exam_type_id')))
            ->when($request->filled('exam_id'), fn (Builder $query) => $query->whereKey($request->integer('exam_id')))
            ->when($request->filled('class_id'), fn (Builder $query) => $query->where('school_class_id', $request->integer('class_id')))
            ->when($request->filled('section_id'), fn (Builder $query) => $query->where('school_section_id', $request->integer('section_id')))
            ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('start_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('end_date', '<=', $request->date_to));

        $this->scopeClassData($query, $request);

        if ($request->export === 'csv') {
            return $this->csv('exam-report.csv',
                ['Exam', 'Type', 'Academic Year', 'Class', 'Section', 'Start Date', 'End Date', 'Schedule', 'Status'],
                $query->orderByDesc('start_date')->get()->map(fn (Exam $exam) => [
                    $exam->exam_name,
                    $exam->examType?->name,
                    $exam->academic_year,
                    $exam->schoolClass?->name,
                    $exam->schoolSection?->name,
                    $exam->start_date?->format('Y-m-d'),
                    $exam->end_date?->format('Y-m-d'),
                    $exam->schedules_count ? 'Scheduled' : 'Not Scheduled',
                    $exam->status,
                ])
            );
        }

        return view('reports.exams', array_merge($this->filters($request), [
            'examRows' => $query->orderByDesc('start_date')->paginate(20)->withQueryString(),
        ]));
    }

    public function marks(Request $request)
    {
        $this->authorizeReport($request, ['super_admin', 'admin', 'teacher', 'student']);

        $query = Mark::query()
            ->with(['exam', 'student', 'subject', 'schoolClass', 'schoolSection'])
            ->when($request->filled('academic_year'), fn (Builder $query) => $query->where('academic_year', $request->academic_year))
            ->when($request->filled('exam_id'), fn (Builder $query) => $query->where('exam_id', $request->integer('exam_id')))
            ->when($request->filled('class_id'), fn (Builder $query) => $query->where('school_class_id', $request->integer('class_id')))
            ->when($request->filled('section_id'), fn (Builder $query) => $query->where('school_section_id', $request->integer('section_id')))
            ->when($request->filled('subject_id'), fn (Builder $query) => $query->where('subject_id', $request->integer('subject_id')))
            ->when($request->filled('student_id'), fn (Builder $query) => $query->where('student_id', $request->integer('student_id')))
            ->when($request->filled('grade'), fn (Builder $query) => $query->where('grade', $request->grade))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->status));

        $this->scopeMarks($query, $request);

        if ($request->export === 'csv') {
            return $this->csv('marks-report.csv',
                ['Student', 'Exam', 'Subject', 'Class', 'Section', 'Marks', 'Maximum', 'Percentage', 'Grade', 'Status'],
                $query->orderByDesc('id')->get()->map(fn (Mark $mark) => [
                    $mark->student?->full_name,
                    $mark->exam?->exam_name,
                    $mark->subject?->name,
                    $mark->schoolClass?->name,
                    $mark->schoolSection?->name,
                    $mark->marks_obtained,
                    $mark->maximum_marks,
                    $mark->maximum_marks ? round(($mark->marks_obtained / $mark->maximum_marks) * 100, 2) : 0,
                    $mark->grade,
                    $mark->status,
                ])
            );
        }

        $totals = (clone $query)->selectRaw('COUNT(*) as records, AVG(CASE WHEN maximum_marks > 0 THEN marks_obtained * 100.0 / maximum_marks END) as average')->first();

        return view('reports.marks', array_merge($this->filters($request), [
            'markRows' => $query->orderByDesc('id')->paginate(20)->withQueryString(),
            'markTotals' => $totals,
        ]));
    }

    public function fees(Request $request)
    {
        $this->authorizeReport($request, ['super_admin', 'admin', 'student']);

        $query = FeeAssignment::query()
            ->with(['student', 'feeType', 'schoolClass', 'schoolSection'])
            ->when($request->filled('academic_year'), fn (Builder $query) => $query->where('academic_year', $request->academic_year))
            ->when($request->filled('fee_type_id'), fn (Builder $query) => $query->where('fee_type_id', $request->integer('fee_type_id')))
            ->when($request->filled('class_id'), fn (Builder $query) => $query->where('school_class_id', $request->integer('class_id')))
            ->when($request->filled('section_id'), fn (Builder $query) => $query->where('school_section_id', $request->integer('section_id')))
            ->when($request->filled('student_id'), fn (Builder $query) => $query->where('student_id', $request->integer('student_id')))
            ->when($request->filled('payment_status'), fn (Builder $query) => $query->where('payment_status', $request->payment_status))
            ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('due_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('due_date', '<=', $request->date_to));

        if ($request->user()->role === 'student') {
            $student = $this->linkedStudent($request);
            $query->where('student_id', $student?->id ?? 0);
        }

        if ($request->export === 'csv') {
            return $this->csv('fee-report.csv',
                ['Student', 'Fee Type', 'Academic Year', 'Class', 'Section', 'Assigned', 'Paid', 'Balance', 'Due Date', 'Status'],
                $query->orderByDesc('due_date')->get()->map(fn (FeeAssignment $fee) => [
                    $fee->student?->full_name,
                    $fee->feeType?->name,
                    $fee->academic_year,
                    $fee->schoolClass?->name,
                    $fee->schoolSection?->name,
                    $fee->assigned_amount,
                    $fee->paid_amount,
                    $fee->balance_amount,
                    $fee->due_date?->format('Y-m-d'),
                    $fee->payment_status,
                ])
            );
        }

        $totals = (clone $query)->selectRaw('SUM(assigned_amount) as assigned, SUM(paid_amount) as paid, SUM(balance_amount) as balance')->first();

        return view('reports.fees', array_merge($this->filters($request), [
            'feeRows' => $query->orderByDesc('due_date')->paginate(20)->withQueryString(),
            'feeTotals' => $totals,
        ]));
    }

    public function classes(Request $request)
    {
        $this->authorizeReport($request, ['super_admin', 'admin', 'teacher']);

        $sections = SchoolSection::query()
            ->with(['schoolClass.subjectAssignments.subject', 'schoolClass.teacherAssignments.teacher'])
            ->when($request->filled('class_id'), fn (Builder $query) => $query->where('school_class_id', $request->integer('class_id')))
            ->when($request->filled('section_id'), fn (Builder $query) => $query->whereKey($request->integer('section_id')));

        if ($request->user()->role === 'teacher') {
            $sections->whereIn('school_class_id', $this->teacherClassIds($request));
        }

        $rows = $sections->orderBy('school_class_id')->orderBy('name')->get()->map(function (SchoolSection $section) use ($request) {
            $students = Student::query()->where('school_class_id', $section->school_class_id)->where('school_section_id', $section->id);
            if ($request->filled('academic_year')) {
                $students->whereHas('classAssignments', fn (Builder $query) => $query->where('academic_year', $request->academic_year));
            }

            $attendance = Attendance::query()
                ->where('school_class_id', $section->school_class_id)
                ->where('school_section_id', $section->id)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            return (object) [
                'class' => $section->schoolClass?->name,
                'section' => $section->name,
                'students' => $students->count(),
                'subjects' => $section->schoolClass?->subjectAssignments->pluck('subject.name')->filter()->unique()->join(', '),
                'teachers' => $section->schoolClass?->teacherAssignments->pluck('teacher.full_name')->filter()->unique()->join(', '),
                'attendance' => $attendance?->total ? round(($attendance->present / $attendance->total) * 100, 2) : 0,
                'exams' => Exam::query()
                    ->where('school_class_id', $section->school_class_id)
                    ->where('school_section_id', $section->id)
                    ->when($request->filled('academic_year'), fn (Builder $query) => $query->where('academic_year', $request->academic_year))
                    ->count(),
            ];
        });

        if ($request->export === 'csv') {
            return $this->csv('class-report.csv',
                ['Class', 'Section', 'Students', 'Subjects', 'Teachers', 'Attendance %', 'Exams'],
                $rows->map(fn ($row) => (array) $row)
            );
        }

        return view('reports.classes', array_merge($this->filters($request), ['classRows' => $rows]));
    }

    public function academicYears(Request $request)
    {
        $this->authorizeReport($request, ['super_admin', 'admin', 'teacher', 'student']);

        $years = $request->filled('academic_year')
            ? collect([$request->academic_year])
            : $this->availableAcademicYears();

        $rows = $years->map(function (string $year) use ($request) {
            $studentIds = StudentClassAssignment::query()
                ->where('academic_year', $year)
                ->when($request->filled('class_id'), fn (Builder $query) => $query->where('school_class_id', $request->integer('class_id')))
                ->when($request->filled('section_id'), fn (Builder $query) => $query->where('school_section_id', $request->integer('section_id')));

            $classIds = null;
            if ($request->user()->role === 'teacher') {
                $classIds = $this->teacherClassIds($request);
                $studentIds->whereIn('school_class_id', $classIds);
            } elseif ($request->user()->role === 'student') {
                $studentIds->where('student_id', $this->linkedStudent($request)?->id ?? 0);
            }

            $ids = $studentIds->pluck('student_id')->unique();
            $attendance = Attendance::query()->whereIn('student_id', $ids)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            $exams = Exam::query()->where('academic_year', $year);
            $fees = FeeAssignment::query()->where('academic_year', $year);
            $results = Result::query()->where('academic_year', $year);

            if ($classIds !== null) {
                $exams->whereIn('school_class_id', $classIds);
                $fees->whereIn('school_class_id', $classIds);
                $results->whereIn('school_class_id', $classIds);
            }
            if ($request->filled('class_id')) {
                foreach ([$exams, $fees, $results] as $query) {
                    $query->where('school_class_id', $request->integer('class_id'));
                }
            }
            if ($request->filled('section_id')) {
                foreach ([$exams, $fees, $results] as $query) {
                    $query->where('school_section_id', $request->integer('section_id'));
                }
            }
            if ($request->user()->role === 'student') {
                $studentId = $this->linkedStudent($request)?->id ?? 0;
                $fees->where('student_id', $studentId);
                $results->where('student_id', $studentId)->where('status', 'published');
                $exams->where('status', 'published');
            }

            $feeTotals = $fees->selectRaw('SUM(assigned_amount) as assigned, SUM(paid_amount) as paid, SUM(balance_amount) as balance')->first();

            return (object) [
                'year' => $year,
                'students' => $ids->count(),
                'classes' => $studentIds->distinct()->count('school_class_id'),
                'exams' => $exams->count(),
                'attendance' => $attendance?->total ? round(($attendance->present / $attendance->total) * 100, 2) : 0,
                'fees_assigned' => $feeTotals?->assigned ?? 0,
                'fees_paid' => $feeTotals?->paid ?? 0,
                'fees_balance' => $feeTotals?->balance ?? 0,
                'passed' => (clone $results)->where('result_status', 'pass')->count(),
                'failed' => (clone $results)->where('result_status', 'fail')->count(),
            ];
        });

        if ($request->export === 'csv') {
            return $this->csv('academic-year-report.csv',
                ['Academic Year', 'Students', 'Classes', 'Exams', 'Attendance %', 'Fees Assigned', 'Fees Paid', 'Fees Balance', 'Passed', 'Failed'],
                $rows->map(fn ($row) => (array) $row)
            );
        }

        return view('reports.academic-years', array_merge($this->filters($request), ['yearRows' => $rows]));
    }

    private function filters(Request $request): array
    {
        $classes = SchoolClass::query()->orderBy('name');
        $students = Student::query()->orderBy('full_name');
        $subjects = Subject::query()->orderBy('name');
        $exams = Exam::query()->orderByDesc('start_date');

        if ($request->user()->role === 'teacher') {
            $classIds = $this->teacherClassIds($request);
            $classes->whereIn('id', $classIds);
            $students->whereIn('school_class_id', $classIds);
            $subjects->whereIn('id', $this->teacherSubjectIds($request));
            $exams->whereIn('school_class_id', $classIds);
        } elseif ($request->user()->role === 'student') {
            $student = $this->linkedStudent($request);
            $classes->whereKey($student?->school_class_id ?? 0);
            $students->whereKey($student?->id ?? 0);
            $exams->where('school_class_id', $student?->school_class_id ?? 0)->where('status', 'published');
        }

        return [
            'classes' => $classes->get(),
            'sections' => SchoolSection::orderBy('name')->get(),
            'studentsFilter' => $students->get(),
            'subjects' => $subjects->get(),
            'examsFilter' => $exams->get(),
            'examTypes' => ExamType::orderBy('name')->get(),
            'feeTypes' => FeeType::orderBy('name')->get(),
            'academicYears' => $this->availableAcademicYears(),
        ];
    }

    private function availableAcademicYears(): Collection
    {
        return collect()
            ->merge(StudentClassAssignment::query()->distinct()->pluck('academic_year'))
            ->merge(Exam::query()->distinct()->pluck('academic_year'))
            ->merge(Mark::query()->distinct()->pluck('academic_year'))
            ->merge(FeeAssignment::query()->distinct()->pluck('academic_year'))
            ->filter()->unique()->sortDesc()->values();
    }

    private function authorizeReport(Request $request, array $roles): void
    {
        abort_unless(in_array($request->user()->role, $roles, true), 403);
    }

    private function scopeStudents(Builder $query, Request $request): void
    {
        if ($request->user()->role === 'teacher') {
            $query->whereIn('school_class_id', $this->teacherClassIds($request));
        } elseif ($request->user()->role === 'student') {
            $query->whereKey($this->linkedStudent($request)?->id ?? 0);
        }
    }

    private function scopeClassData(Builder $query, Request $request, bool $studentColumn = false): void
    {
        if ($request->user()->role === 'teacher') {
            $query->whereIn('school_class_id', $this->teacherClassIds($request));
        } elseif ($request->user()->role === 'student') {
            $student = $this->linkedStudent($request);
            if ($studentColumn) {
                $query->where('student_id', $student?->id ?? 0);
            } else {
                $query->where('school_class_id', $student?->school_class_id ?? 0)
                    ->where('school_section_id', $student?->school_section_id ?? 0)
                    ->where('status', 'published');
            }
        }
    }

    private function scopeMarks(Builder $query, Request $request): void
    {
        if ($request->user()->role === 'teacher') {
            $assignments = $this->teacherAssignments($request);
            $query->where(function (Builder $query) use ($assignments) {
                foreach ($assignments as $assignment) {
                    $query->orWhere(fn (Builder $pair) => $pair
                        ->where('school_class_id', $assignment->school_class_id)
                        ->where('subject_id', $assignment->subject_id));
                }
            });
        } elseif ($request->user()->role === 'student') {
            $query->where('student_id', $this->linkedStudent($request)?->id ?? 0)->where('status', 'published');
        }
    }

    private function linkedStudent(Request $request): ?Student
    {
        return Student::query()->where('email', $request->user()->email)->first();
    }

    private function teacherAssignments(Request $request): Collection
    {
        $teacher = Teacher::query()->where('email', $request->user()->email)->first();

        return $teacher
            ? TeacherSubjectAssignment::query()->where('teacher_id', $teacher->id)->where('status', 'active')->get()
            : collect();
    }

    private function teacherClassIds(Request $request): Collection
    {
        return $this->teacherAssignments($request)->pluck('school_class_id')->unique()->values();
    }

    private function teacherSubjectIds(Request $request): Collection
    {
        return $this->teacherAssignments($request)->pluck('subject_id')->unique()->values();
    }

    private function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, array_values((array) $row));
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
