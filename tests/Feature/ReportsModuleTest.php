<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\FeeAssignment;
use App\Models\FeeType;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_every_report_and_export_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([
            'reports.students',
            'reports.teachers',
            'reports.attendance',
            'reports.exams',
            'reports.marks',
            'reports.fees',
            'reports.classes',
            'reports.academic_years',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        $this->actingAs($admin)
            ->get(route('reports.students', ['export' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_teacher_reports_are_limited_to_assigned_classes_and_subjects(): void
    {
        [$assignedClass, $assignedSection] = $this->classSection('Grade 10', 'A');
        [$otherClass, $otherSection] = $this->classSection('Grade 11', 'B');
        $subject = Subject::factory()->create(['name' => 'Mathematics']);
        $teacherUser = User::factory()->create(['role' => 'teacher', 'email' => 'teacher@school.test']);
        $teacher = Teacher::factory()->create(['email' => 'teacher@school.test']);

        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $assignedClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        Student::factory()->create([
            'full_name' => 'Assigned Student',
            'school_class_id' => $assignedClass->id,
            'school_section_id' => $assignedSection->id,
        ]);
        Student::factory()->create([
            'full_name' => 'Hidden Student',
            'school_class_id' => $otherClass->id,
            'school_section_id' => $otherSection->id,
        ]);

        $this->actingAs($teacherUser)
            ->get(route('reports.students'))
            ->assertOk()
            ->assertSee('Assigned Student')
            ->assertDontSee('Hidden Student');

        $this->actingAs($teacherUser)->get(route('reports.fees'))->assertForbidden();
        $this->actingAs($teacherUser)->get(route('reports.teachers'))->assertForbidden();
    }

    public function test_student_reports_show_only_the_linked_students_records(): void
    {
        [$class, $section] = $this->classSection();
        $studentUser = User::factory()->create(['role' => 'student', 'email' => 'student@school.test']);
        $student = Student::factory()->create([
            'full_name' => 'Linked Student',
            'email' => 'student@school.test',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
        ]);
        $other = Student::factory()->create([
            'full_name' => 'Other Student',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
        ]);
        $subject = Subject::factory()->create();
        StudentClassAssignment::create([
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'academic_year' => '2026/2027',
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);
        $examType = ExamType::firstOrCreate(['name' => 'Term Test']);
        $exam = Exam::create([
            'exam_name' => 'First Term Exam',
            'exam_type_id' => $examType->id,
            'academic_year' => '2026/2027',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'start_date' => today()->addWeek(),
            'end_date' => today()->addWeeks(2),
            'status' => 'published',
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'attendance_date' => today(),
            'status' => 'present',
            'recorded_by' => $studentUser->id,
        ]);
        Attendance::create([
            'student_id' => $other->id,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'attendance_date' => today(),
            'status' => 'absent',
            'recorded_by' => $studentUser->id,
        ]);
        Mark::create([
            'exam_id' => $exam->id,
            'academic_year' => '2026/2027',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'subject_id' => $subject->id,
            'student_id' => $student->id,
            'marks_obtained' => 80,
            'maximum_marks' => 100,
            'grade' => 'A',
            'status' => 'published',
        ]);
        $feeType = FeeType::create([
            'name' => 'Tuition',
            'amount' => 1000,
            'frequency' => 'monthly',
            'status' => 'active',
        ]);
        FeeAssignment::create([
            'academic_year' => '2026/2027',
            'fee_type_id' => $feeType->id,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'student_id' => $student->id,
            'assigned_amount' => 1000,
            'paid_amount' => 250,
            'balance_amount' => 750,
            'due_date' => today()->addWeek(),
            'payment_status' => 'partially_paid',
            'status' => 'active',
        ]);

        $this->actingAs($studentUser)->get(route('reports.students'))
            ->assertOk()->assertSee('Linked Student')->assertDontSee('Other Student');
        $this->actingAs($studentUser)->get(route('reports.attendance'))
            ->assertOk()->assertSee('Linked Student')->assertDontSee('Other Student');
        $this->actingAs($studentUser)->get(route('reports.marks'))
            ->assertOk()->assertSee('Linked Student');
        $this->actingAs($studentUser)->get(route('reports.fees'))
            ->assertOk()->assertSee('Linked Student');
        $this->actingAs($studentUser)->get(route('reports.academic_years'))
            ->assertOk()->assertSee('2026/2027');
        $this->actingAs($studentUser)->get(route('reports.classes'))->assertForbidden();
    }

    private function classSection(string $className = 'Grade 10', string $sectionName = 'A'): array
    {
        $class = SchoolClass::factory()->create(['name' => $className]);
        $section = SchoolSection::factory()->create([
            'school_class_id' => $class->id,
            'name' => $sectionName,
        ]);

        return [$class, $section];
    }
}
