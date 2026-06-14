<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamType;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExaminationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_filter_and_delete_an_exam(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class, $section] = $this->classAndSection();
        $type = ExamType::firstOrFail();

        $this->actingAs($admin)
            ->post(route('examinations.store'), [
                'exam_name' => 'First Term Examination',
                'exam_type_id' => $type->id,
                'academic_year' => '2026/2027',
                'school_class_id' => $class->id,
                'school_section_id' => $section->id,
                'start_date' => '2026-08-10',
                'end_date' => '2026-08-15',
                'description' => 'First term assessment.',
                'status' => 'draft',
            ])
            ->assertRedirect(route('examinations.index'))
            ->assertSessionHas('success');

        $exam = Exam::firstOrFail();

        $this->actingAs($admin)
            ->get(route('examinations.index', [
                'search' => 'First Term',
                'class_id' => $class->id,
                'exam_type_id' => $type->id,
                'academic_year' => '2026/2027',
                'status' => 'draft',
            ]))
            ->assertOk()
            ->assertSee('First Term Examination');

        $this->actingAs($admin)
            ->put(route('examinations.update', $exam), [
                'exam_name' => 'First Term Published',
                'exam_type_id' => $type->id,
                'academic_year' => '2026/2027',
                'school_class_id' => $class->id,
                'school_section_id' => $section->id,
                'start_date' => '2026-08-10',
                'end_date' => '2026-08-16',
                'description' => null,
                'status' => 'published',
            ])
            ->assertRedirect(route('examinations.index'));

        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'exam_name' => 'First Term Published',
            'status' => 'published',
        ]);

        $this->actingAs($admin)
            ->delete(route('examinations.destroy', $exam))
            ->assertRedirect(route('examinations.index'));

        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    public function test_exam_requires_a_section_from_the_selected_class(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class] = $this->classAndSection();
        [, $otherSection] = $this->classAndSection('Grade 11', 'B');

        $this->actingAs($admin)
            ->post(route('examinations.store'), [
                'exam_name' => 'Invalid Exam',
                'exam_type_id' => ExamType::firstOrFail()->id,
                'academic_year' => '2026/2027',
                'school_class_id' => $class->id,
                'school_section_id' => $otherSection->id,
                'start_date' => '2026-08-10',
                'end_date' => '2026-08-15',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('school_section_id');
    }

    public function test_admin_can_manage_exam_schedules_and_date_must_be_inside_exam_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $exam = $this->exam();
        $subject = Subject::create([
            'code' => 'MAT-101',
            'name' => 'Mathematics',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('examinations.schedules.store'), [
                'exam_id' => $exam->id,
                'subject_id' => $subject->id,
                'exam_date' => '2026-08-20',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'room' => 'Hall A',
            ])
            ->assertSessionHasErrors('exam_date');

        $this->actingAs($admin)
            ->post(route('examinations.schedules.store'), [
                'exam_id' => $exam->id,
                'subject_id' => $subject->id,
                'exam_date' => '2026-08-12',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'room' => 'Hall A',
            ])
            ->assertRedirect(route('examinations.schedules.index', ['exam_id' => $exam->id]));

        $schedule = ExamSchedule::firstOrFail();

        $this->actingAs($admin)
            ->put(route('examinations.schedules.update', $schedule), [
                'exam_id' => $exam->id,
                'subject_id' => $subject->id,
                'exam_date' => '2026-08-13',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'room' => 'Hall B',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('exam_schedules', ['id' => $schedule->id, 'room' => 'Hall B']);

        $this->actingAs($admin)
            ->delete(route('examinations.schedules.destroy', $schedule))
            ->assertRedirect();

        $this->assertDatabaseMissing('exam_schedules', ['id' => $schedule->id]);
    }

    public function test_teacher_and_student_have_read_only_scoped_access(): void
    {
        [$class, $section] = $this->classAndSection();
        [$otherClass, $otherSection] = $this->classAndSection('Grade 11', 'B');
        $visibleExam = $this->exam($class, $section, ['exam_name' => 'Visible Published Exam']);
        $this->exam($otherClass, $otherSection, ['exam_name' => 'Other Class Exam']);
        $this->exam($class, $section, ['exam_name' => 'Hidden Draft Exam', 'status' => 'draft']);

        $teacherUser = User::factory()->create(['role' => 'teacher', 'email' => 'teacher@example.com']);
        $teacher = Teacher::create([
            'employee_number' => 'T-100',
            'full_name' => 'Class Teacher',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'School Road',
            'contact_number' => '0771234567',
            'email' => 'teacher@example.com',
            'qualification' => 'B.Ed',
            'experience' => 5,
            'main_subject' => 'Mathematics',
            'joining_date' => '2020-01-01',
            'status' => 'active',
        ]);
        $subject = Subject::create(['code' => 'SCI-101', 'name' => 'Science', 'status' => 'active']);
        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($teacherUser)
            ->get(route('examinations.index'))
            ->assertOk()
            ->assertSee($visibleExam->exam_name)
            ->assertDontSee('Other Class Exam')
            ->assertDontSee('Hidden Draft Exam');

        $this->actingAs($teacherUser)
            ->get(route('examinations.create'))
            ->assertForbidden();

        $studentUser = User::factory()->create(['role' => 'student', 'email' => 'student@example.com']);
        Student::create([
            'admission_number' => 'ST-100',
            'full_name' => 'Assigned Student',
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'address' => 'School Road',
            'contact_number' => '0711234567',
            'email' => 'student@example.com',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'class' => $class->name,
            'section' => $section->name,
            'admission_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $this->actingAs($studentUser)
            ->get(route('examinations.class_exams'))
            ->assertOk()
            ->assertSee($visibleExam->exam_name)
            ->assertDontSee('Other Class Exam')
            ->assertDontSee('Hidden Draft Exam');
    }

    private function classAndSection(string $className = 'Grade 10', string $sectionName = 'A'): array
    {
        $class = SchoolClass::create(['name' => $className, 'capacity' => 40, 'status' => 'active']);
        $section = SchoolSection::create([
            'school_class_id' => $class->id,
            'name' => $sectionName,
            'capacity' => 40,
            'status' => 'active',
        ]);

        return [$class, $section];
    }

    private function exam(?SchoolClass $class = null, ?SchoolSection $section = null, array $overrides = []): Exam
    {
        if (! $class || ! $section) {
            [$class, $section] = $this->classAndSection();
        }

        return Exam::create(array_merge([
            'exam_name' => 'Published Exam',
            'exam_type_id' => ExamType::firstOrFail()->id,
            'academic_year' => '2026/2027',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-15',
            'status' => 'published',
        ], $overrides));
    }
}
