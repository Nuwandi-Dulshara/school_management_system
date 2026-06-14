<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_and_admin_can_access_attendance_but_other_roles_cannot(): void
    {
        foreach (['super_admin', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('attendance.mark'))
                ->assertOk()
                ->assertSee('Attendance Management')
                ->assertSee('Mark Attendance');
        }

        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get(route('attendance.mark'))
            ->assertForbidden();
    }

    public function test_attendance_can_be_marked_for_every_active_student_in_a_class_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$schoolClass, $section] = $this->createClassAndSection();
        $students = Student::factory()->count(3)->create([
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('attendance.store'), [
            'attendance_date' => '2026-06-13',
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'attendance' => [
                $students[0]->id => 'present',
                $students[1]->id => 'absent',
                $students[2]->id => 'late',
            ],
        ]);

        $response->assertRedirect(route('attendance.index', [
            'date' => '2026-06-13',
            'class_id' => $schoolClass->id,
            'section_id' => $section->id,
        ]));

        $this->assertDatabaseCount('attendances', 3);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[2]->id,
            'status' => 'late',
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_marking_attendance_requires_all_students_and_rejects_duplicates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$schoolClass, $section] = $this->createClassAndSection();
        $students = Student::factory()->count(2)->create([
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'status' => 'active',
        ]);

        $payload = [
            'attendance_date' => '2026-06-13',
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'attendance' => [
                $students[0]->id => 'present',
                $students[1]->id => 'absent',
            ],
        ];

        $this->actingAs($admin)->post(route('attendance.store'), [
            ...$payload,
            'attendance' => [$students[0]->id => 'present'],
        ])->assertSessionHasErrors('attendance');

        $this->actingAs($admin)->post(route('attendance.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)->post(route('attendance.store'), $payload)
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseCount('attendances', 2);
    }

    public function test_student_and_section_must_match_the_selected_class_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$firstClass, $firstSection] = $this->createClassAndSection('Grade 8', 'A');
        [$secondClass, $secondSection] = $this->createClassAndSection('Grade 9', 'B');
        $student = Student::factory()->create([
            'school_class_id' => $firstClass->id,
            'school_section_id' => $firstSection->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('attendance.store'), [
            'attendance_date' => '2026-06-13',
            'school_class_id' => $secondClass->id,
            'school_section_id' => $firstSection->id,
            'attendance' => [$student->id => 'present'],
        ])->assertSessionHasErrors('section_id');

        $this->actingAs($admin)->post(route('attendance.store'), [
            'attendance_date' => '2026-06-13',
            'school_class_id' => $secondClass->id,
            'school_section_id' => $secondSection->id,
            'attendance' => [$student->id => 'present'],
        ])->assertSessionHasErrors('attendance');
    }

    public function test_attendance_can_be_updated_and_deleted(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $attendance = $this->createAttendance('present');

        $this->actingAs($superAdmin)
            ->put(route('attendance.update', $attendance), ['status' => 'late'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'late',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('attendance.destroy', $attendance))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);
    }

    public function test_class_and_daily_reports_show_attendance_totals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$schoolClass, $section] = $this->createClassAndSection();

        foreach (['present', 'present', 'absent', 'late'] as $status) {
            $student = Student::factory()->create([
                'school_class_id' => $schoolClass->id,
                'school_section_id' => $section->id,
            ]);

            Attendance::create([
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'school_section_id' => $section->id,
                'attendance_date' => '2026-06-13',
                'status' => $status,
                'recorded_by' => $admin->id,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('attendance.class_report', [
                'class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'date_from' => '2026-06-13',
                'date_to' => '2026-06-13',
            ]))
            ->assertOk()
            ->assertSee('Total Present')
            ->assertSee('Total Absent')
            ->assertSee('Total Late');

        $this->actingAs($admin)
            ->get(route('attendance.daily_report', ['date' => '2026-06-13']))
            ->assertOk()
            ->assertSee($schoolClass->name)
            ->assertSee($section->name);
    }

    private function createAttendance(string $status): Attendance
    {
        [$schoolClass, $section] = $this->createClassAndSection();
        $student = Student::factory()->create([
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
        ]);

        return Attendance::create([
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'school_section_id' => $section->id,
            'attendance_date' => '2026-06-13',
            'status' => $status,
        ]);
    }

    private function createClassAndSection(string $className = 'Grade 10', string $sectionName = 'A'): array
    {
        $schoolClass = SchoolClass::factory()->create([
            'name' => $className,
            'status' => 'active',
        ]);
        $section = SchoolSection::factory()->create([
            'school_class_id' => $schoolClass->id,
            'name' => $sectionName,
            'status' => 'active',
        ]);

        return [$schoolClass, $section];
    }
}
