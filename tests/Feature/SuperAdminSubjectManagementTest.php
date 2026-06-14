<?php

namespace Tests\Feature;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_subject_management(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.subjects.index'))
            ->assertOk()
            ->assertSee('Subject List');

        $this->actingAs($teacher)
            ->get(route('super_admin.subjects.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_update_search_and_toggle_subject(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.subjects.store'), $this->subjectPayload())
            ->assertRedirect();

        $subject = Subject::where('code', 'MAT-101')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('super_admin.subjects.show', $subject))
            ->assertOk()
            ->assertSee('Subject Details');

        $this->actingAs($superAdmin)
            ->get(route('super_admin.subjects.index', ['search' => 'Mathematics']))
            ->assertOk()
            ->assertSee('Mathematics');

        $this->actingAs($superAdmin)
            ->put(route('super_admin.subjects.update', $subject), $this->subjectPayload([
                'name' => 'Advanced Mathematics',
            ]))
            ->assertRedirect(route('super_admin.subjects.show', $subject));

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.subjects.status', $subject))
            ->assertRedirect();

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Advanced Mathematics',
            'status' => 'inactive',
        ]);
    }

    public function test_subject_code_and_name_must_be_unique(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        Subject::factory()->create([
            'code' => 'MAT-101',
            'name' => 'Mathematics',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.subjects.store'), $this->subjectPayload())
            ->assertSessionHasErrors(['code', 'name']);
    }

    public function test_super_admin_can_manage_class_subject_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create();
        $otherClass = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('super_admin.subjects.classes.store'), [
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'active',
            ])
            ->assertRedirect();

        $assignment = ClassSubject::firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('super_admin.subjects.classes'))
            ->assertOk()
            ->assertSee($schoolClass->name)
            ->assertSee($subject->name);

        $this->actingAs($superAdmin)
            ->put(route('super_admin.subjects.classes.update', $assignment), [
                'school_class_id' => $otherClass->id,
                'subject_id' => $subject->id,
                'status' => 'inactive',
            ])
            ->assertRedirect(route('super_admin.subjects.classes'));

        $this->assertDatabaseHas('class_subjects', [
            'id' => $assignment->id,
            'school_class_id' => $otherClass->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.subjects.classes.destroy', $assignment))
            ->assertRedirect();

        $this->assertDatabaseMissing('class_subjects', ['id' => $assignment->id]);
    }

    public function test_duplicate_class_subject_assignment_is_rejected(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();
        ClassSubject::create([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.subjects.classes.store'), [
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('subject_id');
    }

    public function test_inactive_class_or_subject_cannot_receive_active_assignment(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create(['status' => 'inactive']);
        $subject = Subject::factory()->create(['status' => 'active']);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.subjects.classes.store'), [
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_deactivating_or_deleting_subject_updates_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();
        $assignment = ClassSubject::create([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.subjects.status', $subject))
            ->assertRedirect();

        $this->assertDatabaseHas('class_subjects', [
            'id' => $assignment->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.subjects.destroy', $subject))
            ->assertRedirect(route('super_admin.subjects.index'));

        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
        $this->assertDatabaseMissing('class_subjects', ['id' => $assignment->id]);
    }

    private function subjectPayload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'MAT-101',
            'name' => 'Mathematics',
            'description' => 'Core mathematics subject.',
            'status' => 'active',
        ], $overrides);
    }
}
