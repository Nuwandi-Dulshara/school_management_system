<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_teacher_management(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacherUser = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.teachers.index'))
            ->assertOk()
            ->assertSee('Teacher List');

        $this->actingAs($teacherUser)
            ->get(route('super_admin.teachers.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_and_update_teacher(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)
            ->post(route('super_admin.teachers.store'), $this->teacherPayload());

        $teacher = Teacher::where('employee_number', 'TCH-2026-001')->firstOrFail();

        $response->assertRedirect(route('super_admin.teachers.show', $teacher));
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'full_name' => 'Kamal Silva',
            'main_subject' => 'Mathematics',
        ]);

        $this->actingAs($superAdmin)
            ->put(route('super_admin.teachers.update', $teacher), $this->teacherPayload([
                'full_name' => 'Kamal Updated',
                'status' => 'inactive',
            ]))
            ->assertRedirect(route('super_admin.teachers.show', $teacher));

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'full_name' => 'Kamal Updated',
            'status' => 'inactive',
        ]);
    }

    public function test_employee_number_and_email_must_be_unique(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        Teacher::factory()->create([
            'employee_number' => 'TCH-2026-001',
            'email' => 'kamal@example.test',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.teachers.store'), $this->teacherPayload())
            ->assertSessionHasErrors(['employee_number', 'email']);
    }

    public function test_super_admin_can_search_and_toggle_teacher_status(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create([
            'full_name' => 'Searchable Teacher',
            'main_subject' => 'Science',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.teachers.index', ['search' => 'Searchable', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Searchable Teacher');

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.teachers.status', $teacher))
            ->assertRedirect();

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id, 'status' => 'inactive']);
    }

    public function test_super_admin_can_manage_class_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('super_admin.teachers.classes.store', $teacher), [
                'class_name' => 'Grade 10',
                'section' => 'A',
                'academic_year' => '2026',
                'status' => 'active',
            ])
            ->assertRedirect();

        $assignment = $teacher->assignedClasses()->firstOrFail();

        $this->actingAs($superAdmin)
            ->put(route('super_admin.teachers.classes.update', [$teacher, $assignment]), [
                'class_name' => 'Grade 11',
                'section' => 'B',
                'academic_year' => '2027',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('super_admin.teachers.classes', $teacher));

        $this->assertDatabaseHas('teacher_assigned_classes', [
            'id' => $assignment->id,
            'class_name' => 'Grade 11',
            'status' => 'inactive',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.teachers.classes.destroy', [$teacher, $assignment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('teacher_assigned_classes', ['id' => $assignment->id]);
    }

    public function test_super_admin_can_manage_subject_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('super_admin.teachers.subjects.store', $teacher), [
                'subject_name' => 'Physics',
                'class_name' => 'Grade 12',
                'section' => 'A',
                'academic_year' => '2026',
                'status' => 'active',
            ])
            ->assertRedirect();

        $assignment = $teacher->assignedSubjects()->firstOrFail();

        $this->actingAs($superAdmin)
            ->put(route('super_admin.teachers.subjects.update', [$teacher, $assignment]), [
                'subject_name' => 'Advanced Physics',
                'class_name' => 'Grade 13',
                'section' => 'B',
                'academic_year' => '2027',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('super_admin.teachers.subjects', $teacher));

        $this->assertDatabaseHas('teacher_assigned_subjects', [
            'id' => $assignment->id,
            'subject_name' => 'Advanced Physics',
            'status' => 'inactive',
        ]);
    }

    public function test_assignment_cannot_be_changed_through_another_teacher(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $assignment = $teacher->assignedClasses()->create([
            'class_name' => 'Grade 10',
            'section' => 'A',
            'academic_year' => '2026',
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.teachers.classes.destroy', [$otherTeacher, $assignment]))
            ->assertNotFound();

        $this->assertDatabaseHas('teacher_assigned_classes', ['id' => $assignment->id]);
    }

    public function test_deleting_teacher_removes_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = Teacher::factory()->create();
        $teacher->assignedClasses()->create([
            'class_name' => 'Grade 10',
            'section' => 'A',
            'academic_year' => '2026',
            'status' => 'active',
        ]);
        $teacher->assignedSubjects()->create([
            'subject_name' => 'Mathematics',
            'class_name' => 'Grade 10',
            'section' => 'A',
            'academic_year' => '2026',
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.teachers.destroy', $teacher))
            ->assertRedirect(route('super_admin.teachers.index'));

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseMissing('teacher_assigned_classes', ['teacher_id' => $teacher->id]);
        $this->assertDatabaseMissing('teacher_assigned_subjects', ['teacher_id' => $teacher->id]);
    }

    private function teacherPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Kamal Silva',
            'employee_number' => 'TCH-2026-001',
            'date_of_birth' => '1985-05-10',
            'gender' => 'male',
            'address' => 'Colombo',
            'contact_number' => '0711234567',
            'email' => 'kamal@example.test',
            'qualification' => 'B.Ed.',
            'experience' => 12,
            'main_subject' => 'Mathematics',
            'joining_date' => '2020-01-05',
            'status' => 'active',
        ], $overrides);
    }
}
