<?php

namespace Tests\Feature;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTeacherSubjectAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_teacher_subject_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacherUser = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.teacher_assignments.index'))
            ->assertOk()
            ->assertSee('Teacher Assignment List');

        $this->actingAs($teacherUser)
            ->get(route('super_admin.teacher_assignments.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_update_view_toggle_and_remove_an_assignment(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        [$schoolClass, $subject] = $this->createClassSubject('Grade 10', 'MAT-100', 'Mathematics');
        [$otherClass, $otherSubject] = $this->createClassSubject('Grade 11', 'SCI-100', 'Science');

        $this->actingAs($superAdmin)
            ->post(route('super_admin.teacher_assignments.store'), [
                'teacher_id' => $teacher->id,
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'active',
            ])
            ->assertRedirect();

        $assignment = TeacherSubjectAssignment::firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('super_admin.teacher_assignments.show', $assignment))
            ->assertOk()
            ->assertSee($teacher->full_name)
            ->assertSee($subject->name);

        $this->actingAs($superAdmin)
            ->put(route('super_admin.teacher_assignments.update', $assignment), [
                'teacher_id' => $otherTeacher->id,
                'school_class_id' => $otherClass->id,
                'subject_id' => $otherSubject->id,
                'status' => 'active',
            ])
            ->assertRedirect(route('super_admin.teacher_assignments.show', $assignment));

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.teacher_assignments.status', $assignment))
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_subject_assignments', [
            'id' => $assignment->id,
            'teacher_id' => $otherTeacher->id,
            'school_class_id' => $otherClass->id,
            'subject_id' => $otherSubject->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.teacher_assignments.destroy', $assignment))
            ->assertRedirect(route('super_admin.teacher_assignments.index'));

        $this->assertDatabaseMissing('teacher_subject_assignments', ['id' => $assignment->id]);
    }

    public function test_duplicate_teacher_class_subject_assignment_is_rejected(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create();
        [$schoolClass, $subject] = $this->createClassSubject('Grade 10', 'MAT-100', 'Mathematics');

        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.teacher_assignments.store'), [
                'teacher_id' => $teacher->id,
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('teacher_id');

        $this->assertDatabaseCount('teacher_subject_assignments', 1);
    }

    public function test_active_assignment_requires_active_sources_and_class_subject_mapping(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create(['status' => 'inactive']);
        $schoolClass = SchoolClass::factory()->create(['name' => 'Grade 10']);
        $subject = Subject::factory()->create(['code' => 'MAT-100', 'name' => 'Mathematics']);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.teacher_assignments.store'), [
                'teacher_id' => $teacher->id,
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($superAdmin)
            ->post(route('super_admin.teacher_assignments.store'), [
                'teacher_id' => $teacher->id,
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'inactive',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_subject_assignments', [
            'teacher_id' => $teacher->id,
            'status' => 'inactive',
        ]);
    }

    public function test_assignment_lists_can_be_searched_filtered_and_grouped(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create(['full_name' => 'Kamal Silva']);
        [$schoolClass, $subject] = $this->createClassSubject('Grade 10', 'MAT-100', 'Mathematics');
        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.teacher_assignments.index', [
                'search' => 'Kamal',
                'teacher_id' => $teacher->id,
                'class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Kamal Silva')
            ->assertSee('Mathematics');

        $this->actingAs($superAdmin)
            ->get(route('super_admin.teacher_assignments.class_wise', ['class_id' => $schoolClass->id]))
            ->assertOk()
            ->assertSee('Grade 10')
            ->assertSee('Kamal Silva');

        $this->actingAs($superAdmin)
            ->get(route('super_admin.teacher_assignments.subject_wise', ['subject_id' => $subject->id]))
            ->assertOk()
            ->assertSee('Mathematics')
            ->assertSee('Grade 10');
    }

    public function test_deactivating_source_records_deactivates_teaching_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create();
        [$schoolClass, $subject] = $this->createClassSubject('Grade 10', 'MAT-100', 'Mathematics');
        $assignment = TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.teachers.status', $teacher))
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_subject_assignments', [
            'id' => $assignment->id,
            'status' => 'inactive',
        ]);
    }

    public function test_deactivating_class_subject_mapping_deactivates_teaching_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create();
        [$schoolClass, $subject] = $this->createClassSubject('Grade 10', 'MAT-100', 'Mathematics');
        $classSubject = ClassSubject::where([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
        ])->firstOrFail();
        $assignment = TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->put(route('super_admin.subjects.classes.update', $classSubject), [
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'inactive',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_subject_assignments', [
            'id' => $assignment->id,
            'status' => 'inactive',
        ]);
    }

    private function createClassSubject(
        string $className,
        string $subjectCode,
        string $subjectName
    ): array {
        $schoolClass = SchoolClass::factory()->create([
            'name' => $className,
            'status' => 'active',
        ]);
        $subject = Subject::factory()->create([
            'code' => $subjectCode,
            'name' => $subjectName,
            'status' => 'active',
        ]);
        ClassSubject::create([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        return [$schoolClass, $subject];
    }
}
