<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_pages_are_available_to_every_authenticated_role(): void
    {
        foreach (['super_admin', 'admin', 'teacher', 'student'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('profile.index'))->assertOk();
            $this->actingAs($user)->get(route('profile.edit'))->assertOk();
            $this->actingAs($user)->get(route('profile.password.edit'))->assertOk();
        }

        auth()->logout();
        $this->get(route('profile.index'))->assertRedirect(route('login'));
    }

    public function test_student_can_update_only_own_profile_and_linked_record_with_picture(): void
    {
        Storage::fake('public');
        [$class, $section] = $this->classSection();
        $user = User::factory()->create([
            'role' => 'student',
            'email' => 'old@student.test',
            'status' => 'active',
            'profile_picture' => 'profile-pictures/old.jpg',
        ]);
        Storage::disk('public')->put('profile-pictures/old.jpg', 'old');
        $student = Student::factory()->create([
            'email' => 'old@student.test',
            'full_name' => 'Old Student',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
        ]);
        StudentClassAssignment::create([
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'academic_year' => '2026/2027',
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);
        $other = User::factory()->create(['first_name' => 'Other']);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'full_name' => 'Updated Student',
                'username' => 'updated.student',
                'email' => 'updated@student.test',
                'phone' => '0771234567',
                'address' => '12 School Road',
                'profile_picture' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
                'role' => 'super_admin',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('success');

        $user->refresh();
        $student->refresh();
        $other->refresh();

        $this->assertSame('Updated', $user->first_name);
        $this->assertSame('Student', $user->last_name);
        $this->assertSame('student', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertSame('Updated Student', $student->full_name);
        $this->assertSame('updated@student.test', $student->email);
        $this->assertSame('0771234567', $student->contact_number);
        $this->assertSame('Other', $other->first_name);
        Storage::disk('public')->assertExists($user->profile_picture);
        Storage::disk('public')->assertMissing('profile-pictures/old.jpg');

        $this->actingAs($user)
            ->get(route('profile.index'))
            ->assertOk()
            ->assertSee('Updated Student')
            ->assertSee('2026/2027');
    }

    public function test_duplicate_username_and_email_are_rejected(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $existing = User::factory()->create([
            'username' => 'existing',
            'email' => 'existing@example.test',
        ]);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'full_name' => 'Admin User',
                'username' => $existing->username,
                'email' => $existing->email,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors(['username', 'email']);
    }

    public function test_teacher_profile_displays_role_assignments(): void
    {
        [$class] = $this->classSection('Grade 9', 'A');
        $subject = Subject::factory()->create(['name' => 'Science']);
        $user = User::factory()->create(['role' => 'teacher', 'email' => 'teacher@school.test']);
        $teacher = Teacher::factory()->create([
            'email' => 'teacher@school.test',
            'employee_number' => 'T-100',
        ]);
        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('profile.index'))
            ->assertOk()
            ->assertSee('T-100')
            ->assertSee('Grade 9')
            ->assertSee('Science');
    }

    public function test_password_change_requires_current_password_and_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->from(route('profile.password.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('profile.password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
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
