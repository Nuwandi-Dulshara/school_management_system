<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminClassSectionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_class_management(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.classes.index'))
            ->assertOk()
            ->assertSee('Class List');

        $this->actingAs($teacher)
            ->get(route('super_admin.classes.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_update_search_and_toggle_a_class(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.classes.store'), [
                'name' => 'Grade 10',
                'capacity' => 120,
                'status' => 'active',
            ])
            ->assertRedirect();

        $schoolClass = SchoolClass::where('name', 'Grade 10')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('super_admin.classes.index', ['search' => 'Grade 10']))
            ->assertOk()
            ->assertSee('Grade 10');

        $this->actingAs($superAdmin)
            ->put(route('super_admin.classes.update', $schoolClass), [
                'name' => 'Grade 10 Updated',
                'capacity' => 140,
                'status' => 'active',
            ])
            ->assertRedirect(route('super_admin.classes.show', $schoolClass));

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.classes.status', $schoolClass))
            ->assertRedirect();

        $this->assertDatabaseHas('school_classes', [
            'id' => $schoolClass->id,
            'name' => 'Grade 10 Updated',
            'capacity' => 140,
            'status' => 'inactive',
        ]);
    }

    public function test_class_name_and_section_name_within_class_must_be_unique(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create(['name' => 'Grade 10']);
        SchoolSection::factory()->create([
            'school_class_id' => $schoolClass->id,
            'name' => 'A',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.classes.store'), [
                'name' => 'Grade 10',
                'capacity' => 100,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name');

        $this->actingAs($superAdmin)
            ->post(route('super_admin.sections.store'), [
                'school_class_id' => $schoolClass->id,
                'name' => 'A',
                'capacity' => 40,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_super_admin_can_manage_sections(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('super_admin.sections.store'), [
                'school_class_id' => $schoolClass->id,
                'name' => 'A',
                'capacity' => 40,
                'status' => 'active',
            ])
            ->assertRedirect(route('super_admin.sections.index'));

        $section = SchoolSection::where('school_class_id', $schoolClass->id)->firstOrFail();

        $this->actingAs($superAdmin)
            ->put(route('super_admin.sections.update', $section), [
                'school_class_id' => $schoolClass->id,
                'name' => 'B',
                'capacity' => 45,
                'status' => 'active',
            ])
            ->assertRedirect(route('super_admin.sections.index'));

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.sections.status', $section))
            ->assertRedirect();

        $this->assertDatabaseHas('school_sections', [
            'id' => $section->id,
            'name' => 'B',
            'capacity' => 45,
            'status' => 'inactive',
        ]);
    }

    public function test_super_admin_can_assign_and_move_students_between_sections(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create(['name' => 'Grade 10', 'capacity' => 2]);
        $sectionA = SchoolSection::factory()->create([
            'school_class_id' => $schoolClass->id,
            'name' => 'A',
            'capacity' => 2,
        ]);
        $sectionB = SchoolSection::factory()->create([
            'school_class_id' => $schoolClass->id,
            'name' => 'B',
            'capacity' => 2,
        ]);
        $student = Student::factory()->create();

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.classes.students.assign', [$schoolClass, $student]), [
                'school_section_id' => $sectionA->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $sectionA->id,
            'class' => 'Grade 10',
            'section' => 'A',
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.classes.students.assign', [$schoolClass, $student]), [
                'school_section_id' => $sectionB->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_section_id' => $sectionB->id,
            'section' => 'B',
        ]);
    }

    public function test_capacity_cannot_be_reduced_below_enrollment(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create(['capacity' => 2]);
        $section = SchoolSection::factory()->create([
            'school_class_id' => $schoolClass->id,
            'capacity' => 2,
        ]);
        Student::factory()->count(2)->create([
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
        ]);

        $this->actingAs($superAdmin)
            ->put(route('super_admin.classes.update', $schoolClass), [
                'name' => $schoolClass->name,
                'capacity' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('capacity');

        $this->actingAs($superAdmin)
            ->put(route('super_admin.sections.update', $section), [
                'school_class_id' => $schoolClass->id,
                'name' => $section->name,
                'capacity' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('capacity');
    }

    public function test_in_use_classes_and_sections_cannot_be_deleted(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $schoolClass = SchoolClass::factory()->create();
        $section = SchoolSection::factory()->create(['school_class_id' => $schoolClass->id]);
        $student = Student::factory()->create([
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.sections.destroy', $section))
            ->assertSessionHas('error');

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.classes.destroy', $schoolClass))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('school_classes', ['id' => $schoolClass->id]);
        $this->assertDatabaseHas('school_sections', ['id' => $section->id]);
        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }
}
