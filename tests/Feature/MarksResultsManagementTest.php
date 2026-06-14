<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Services\ResultCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarksResultsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enter_marks_and_results_are_calculated_and_ranked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class, $section, $exam, $subject] = $this->academicContext();
        $first = $this->student($class, $section, 'ST-001', 'First Student');
        $second = $this->student($class, $section, 'ST-002', 'Second Student');

        $this->actingAs($admin)
            ->post(route('marks.store'), $this->marksPayload($exam, $class, $section, $subject, [
                ['student_id' => $first->id, 'marks_obtained' => 80, 'maximum_marks' => 100, 'remarks' => 'Excellent'],
                ['student_id' => $second->id, 'marks_obtained' => 60, 'maximum_marks' => 100, 'remarks' => null],
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('marks', [
            'exam_id' => $exam->id,
            'student_id' => $first->id,
            'grade' => 'A',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('results', [
            'exam_id' => $exam->id,
            'student_id' => $first->id,
            'total_marks' => 80,
            'average_marks' => 80,
            'final_grade' => 'A',
            'rank' => 1,
            'result_status' => 'pass',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('results', [
            'student_id' => $second->id,
            'rank' => 2,
            'final_grade' => 'C',
        ]);

        $this->actingAs($admin)
            ->get(route('marks.class_report', ['exam_id' => $exam->id]))
            ->assertOk()
            ->assertSee('First Student')
            ->assertSee('Second Student');

        $this->actingAs($admin)
            ->get(route('marks.result_sheet', ['exam_id' => $exam->id, 'student_id' => $first->id]))
            ->assertOk()
            ->assertSee('Excellent')
            ->assertSee('80.00%');

        $this->actingAs($admin)
            ->get(route('marks.subject_report', [
                'exam_id' => $exam->id,
                'class_id' => $class->id,
                'section_id' => $section->id,
                'subject_id' => $subject->id,
            ]))
            ->assertOk()
            ->assertSee('Pass Rate')
            ->assertSee('First Student');
    }

    public function test_marks_cannot_exceed_maximum_and_duplicate_entry_updates_existing_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class, $section, $exam, $subject] = $this->academicContext();
        $student = $this->student($class, $section);

        $this->actingAs($admin)
            ->post(route('marks.store'), $this->marksPayload($exam, $class, $section, $subject, [
                ['student_id' => $student->id, 'marks_obtained' => 110, 'maximum_marks' => 100],
            ]))
            ->assertSessionHasErrors('marks.0.marks_obtained');

        $this->actingAs($admin)->post(
            route('marks.store'),
            $this->marksPayload($exam, $class, $section, $subject, [
                ['student_id' => $student->id, 'marks_obtained' => 45, 'maximum_marks' => 100],
            ])
        );
        $this->actingAs($admin)->post(
            route('marks.store'),
            $this->marksPayload($exam, $class, $section, $subject, [
                ['student_id' => $student->id, 'marks_obtained' => 70, 'maximum_marks' => 100],
            ])
        );

        $this->assertDatabaseCount('marks', 1);
        $this->assertDatabaseHas('marks', ['student_id' => $student->id, 'marks_obtained' => 70, 'grade' => 'B']);
    }

    public function test_editing_and_deleting_marks_recalculates_results(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class, $section, $exam, $subject] = $this->academicContext();
        $student = $this->student($class, $section);

        $this->actingAs($admin)->post(
            route('marks.store'),
            $this->marksPayload($exam, $class, $section, $subject, [
                ['student_id' => $student->id, 'marks_obtained' => 30, 'maximum_marks' => 100],
            ])
        );
        $mark = Mark::firstOrFail();

        $this->actingAs($admin)
            ->put(route('marks.update', $mark), [
                'marks_obtained' => 75,
                'maximum_marks' => 100,
                'remarks' => 'Improved',
                'status' => 'published',
            ])
            ->assertRedirect(route('marks.index'));

        $this->assertDatabaseHas('results', [
            'student_id' => $student->id,
            'final_grade' => 'A',
            'result_status' => 'pass',
        ]);

        $this->actingAs($admin)->delete(route('marks.destroy', $mark))->assertRedirect();
        $this->assertDatabaseMissing('results', ['student_id' => $student->id, 'exam_id' => $exam->id]);
    }

    public function test_teacher_can_manage_only_assigned_class_and_subject_marks(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher', 'email' => 'teacher@school.test']);
        [$class, $section, $exam, $subject] = $this->academicContext();
        $student = $this->student($class, $section);
        $teacher = Teacher::factory()->create(['email' => 'teacher@school.test', 'status' => 'active']);
        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($teacherUser)
            ->get(route('marks.entry', [
                'exam_id' => $exam->id,
                'class_id' => $class->id,
                'section_id' => $section->id,
                'subject_id' => $subject->id,
            ]))
            ->assertOk()
            ->assertSee($student->full_name);

        $otherSubject = Subject::factory()->create(['status' => 'active']);
        $this->actingAs($teacherUser)
            ->post(route('marks.store'), $this->marksPayload($exam, $class, $section, $otherSubject, [
                ['student_id' => $student->id, 'marks_obtained' => 70, 'maximum_marks' => 100],
            ]))
            ->assertForbidden();
    }

    public function test_student_sees_only_own_published_results(): void
    {
        [$class, $section, $exam, $subject] = $this->academicContext();
        $studentUser = User::factory()->create(['role' => 'student', 'email' => 'student@school.test']);
        $student = $this->student($class, $section, 'ST-100', 'Linked Student', 'student@school.test');
        $otherStudent = $this->student($class, $section, 'ST-200', 'Other Student', 'other@school.test');

        $published = Mark::create($this->markData($exam, $class, $section, $subject, $student, 80, 'published'));
        app(ResultCalculator::class)->refresh($exam->id, $student->id);
        Mark::create($this->markData($exam, $class, $section, $subject, $otherStudent, 90, 'published'));
        app(ResultCalculator::class)->refresh($exam->id, $otherStudent->id);

        $this->actingAs($studentUser)
            ->get(route('marks.student_results'))
            ->assertOk()
            ->assertSee($exam->exam_name)
            ->assertSee($subject->name)
            ->assertDontSee('Other Student');

        $published->update(['status' => 'draft']);
        app(ResultCalculator::class)->refresh($exam->id, $student->id);

        $this->actingAs($studentUser)
            ->get(route('marks.student_results'))
            ->assertOk()
            ->assertDontSee($exam->exam_name);

        $this->actingAs($studentUser)->get(route('marks.index'))->assertForbidden();
    }

    private function academicContext(): array
    {
        $class = SchoolClass::factory()->create(['name' => 'Grade 10', 'status' => 'active']);
        $section = SchoolSection::factory()->create(['school_class_id' => $class->id, 'name' => 'A', 'status' => 'active']);
        $exam = Exam::create([
            'exam_name' => 'Term One Exam',
            'exam_type_id' => ExamType::firstOrFail()->id,
            'academic_year' => '2026/2027',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-15',
            'status' => 'published',
        ]);
        $subject = Subject::factory()->create(['name' => 'Mathematics', 'status' => 'active']);

        return [$class, $section, $exam, $subject];
    }

    private function student(
        SchoolClass $class,
        SchoolSection $section,
        string $admission = 'ST-001',
        string $name = 'Test Student',
        string $email = 'student@example.test'
    ): Student {
        return Student::factory()->create([
            'admission_number' => $admission,
            'full_name' => $name,
            'email' => $email,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'class' => $class->name,
            'section' => $section->name,
            'status' => 'active',
        ]);
    }

    private function marksPayload(
        Exam $exam,
        SchoolClass $class,
        SchoolSection $section,
        Subject $subject,
        array $marks
    ): array {
        return [
            'exam_id' => $exam->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'status' => 'published',
            'marks' => $marks,
        ];
    }

    private function markData(
        Exam $exam,
        SchoolClass $class,
        SchoolSection $section,
        Subject $subject,
        Student $student,
        float $marks,
        string $status
    ): array {
        return [
            'exam_id' => $exam->id,
            'academic_year' => $exam->academic_year,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'subject_id' => $subject->id,
            'student_id' => $student->id,
            'marks_obtained' => $marks,
            'maximum_marks' => 100,
            'grade' => Mark::gradeFor($marks, 100),
            'status' => $status,
        ];
    }
}
