<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminStudentClassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_student_class_assignments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.student_assignments.index'))
            ->assertOk()
            ->assertSee('Class-wise Student List');

        $this->actingAs($teacher)
            ->get(route('super_admin.student_assignments.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_assign_a_student_and_current_student_fields_are_synchronized(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        [$schoolClass, $section] = $this->createClassAndSection('Grade 8', 'A');
        $student = Student::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('super_admin.student_assignments.store'), [
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'school_section_id' => $section->id,
                'academic_year' => '2026/2027',
            ])
            ->assertRedirect(route('super_admin.student_assignments.index', [
                'academic_year' => '2026/2027',
            ]));

        $this->assertDatabaseHas('student_class_assignments', [
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'academic_year' => '2026/2027',
            'status' => 'assigned',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'class' => 'Grade 8',
            'section' => 'A',
        ]);
    }

    public function test_duplicate_or_second_active_assignment_in_the_same_academic_year_is_rejected(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        [$firstClass, $firstSection] = $this->createClassAndSection('Grade 8', 'A');
        [$secondClass, $secondSection] = $this->createClassAndSection('Grade 9', 'B');
        $student = Student::factory()->create();

        StudentClassAssignment::create([
            'student_id' => $student->id,
            'school_class_id' => $firstClass->id,
            'school_section_id' => $firstSection->id,
            'academic_year' => '2026/2027',
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.student_assignments.store'), [
                'student_id' => $student->id,
                'school_class_id' => $secondClass->id,
                'school_section_id' => $secondSection->id,
                'academic_year' => '2026/2027',
            ])
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('student_class_assignments', 1);
    }

    public function test_super_admin_can_transfer_a_student_and_transfer_history_is_retained(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        [$firstClass, $firstSection] = $this->createClassAndSection('Grade 8', 'A');
        [$secondClass, $secondSection] = $this->createClassAndSection('Grade 9', 'B');
        $student = Student::factory()->create([
            'school_class_id' => $firstClass->id,
            'school_section_id' => $firstSection->id,
            'class' => $firstClass->name,
            'section' => $firstSection->name,
        ]);
        $assignment = StudentClassAssignment::create([
            'student_id' => $student->id,
            'school_class_id' => $firstClass->id,
            'school_section_id' => $firstSection->id,
            'academic_year' => '2026/2027',
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.student_assignments.transfer', $assignment), [
                'school_class_id' => $secondClass->id,
                'school_section_id' => $secondSection->id,
            ])
            ->assertRedirect(route('super_admin.student_assignments.transfers', [
                'academic_year' => '2026/2027',
            ]));

        $this->assertDatabaseHas('student_class_assignments', [
            'id' => $assignment->id,
            'status' => 'transferred',
        ]);
        $this->assertDatabaseHas('student_class_assignments', [
            'student_id' => $student->id,
            'school_class_id' => $secondClass->id,
            'school_section_id' => $secondSection->id,
            'academic_year' => '2026/2027',
            'status' => 'assigned',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_class_id' => $secondClass->id,
            'school_section_id' => $secondSection->id,
        ]);
    }

    public function test_super_admin_can_filter_assignments_and_remove_the_current_assignment(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        [$schoolClass, $section] = $this->createClassAndSection('Grade 8', 'A');
        $student = Student::factory()->create([
            'full_name' => 'Nimal Perera',
            'admission_number' => 'ADM-555',
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
        ]);
        $assignment = StudentClassAssignment::create([
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'academic_year' => '2026/2027',
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.student_assignments.index', [
                'search' => 'ADM-555',
                'academic_year' => '2026/2027',
                'class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'status' => 'assigned',
            ]))
            ->assertOk()
            ->assertSee('Nimal Perera');

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.student_assignments.destroy', $assignment))
            ->assertRedirect();

        $this->assertDatabaseHas('student_class_assignments', [
            'id' => $assignment->id,
            'status' => 'removed',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_class_id' => null,
            'school_section_id' => null,
            'class' => 'Unassigned',
            'section' => 'Unassigned',
        ]);
    }

    public function test_existing_class_student_action_also_records_academic_year_history(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        [$schoolClass, $section] = $this->createClassAndSection('Grade 8', 'A');
        $student = Student::factory()->create();

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.classes.students.assign', [$schoolClass, $student]), [
                'school_section_id' => $section->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('student_class_assignments', [
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'academic_year' => now()->year.'/'.(now()->year + 1),
            'status' => 'assigned',
        ]);
    }

    public function test_past_academic_year_assignment_does_not_replace_current_student_fields(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        [$currentClass, $currentSection] = $this->createClassAndSection('Grade 9', 'A');
        [$pastClass, $pastSection] = $this->createClassAndSection('Grade 8', 'B');
        $student = Student::factory()->create([
            'school_class_id' => $currentClass->id,
            'school_section_id' => $currentSection->id,
            'class' => $currentClass->name,
            'section' => $currentSection->name,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.student_assignments.store'), [
                'student_id' => $student->id,
                'school_class_id' => $pastClass->id,
                'school_section_id' => $pastSection->id,
                'academic_year' => '2025/2026',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('student_class_assignments', [
            'student_id' => $student->id,
            'academic_year' => '2025/2026',
            'school_class_id' => $pastClass->id,
            'status' => 'assigned',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_class_id' => $currentClass->id,
            'school_section_id' => $currentSection->id,
        ]);
    }

    private function createClassAndSection(string $className, string $sectionName): array
    {
        $schoolClass = SchoolClass::factory()->create([
            'name' => $className,
            'capacity' => 40,
            'status' => 'active',
        ]);
        $section = SchoolSection::factory()->create([
            'school_class_id' => $schoolClass->id,
            'name' => $sectionName,
            'capacity' => 40,
            'status' => 'active',
        ]);

        return [$schoolClass, $section];
    }
}
