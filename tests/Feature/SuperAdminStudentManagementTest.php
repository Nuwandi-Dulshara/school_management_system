<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_student_management(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $studentUser = User::factory()->create(['role' => 'student']);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.students.index'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('super_admin.students.status'))
            ->assertOk()
            ->assertSee('Student Status');

        $this->actingAs($studentUser)
            ->get(route('super_admin.students.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_register_student_with_guardian_details(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)
            ->post(route('super_admin.students.store'), $this->studentPayload());

        $student = Student::where('admission_number', 'ADM-2026-001')->firstOrFail();

        $response->assertRedirect(route('super_admin.students.show', $student));

        $this->assertDatabaseHas('students', [
            'admission_number' => 'ADM-2026-001',
            'full_name' => 'Amal Perera',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('student_guardians', [
            'student_id' => $student->id,
            'guardian_name' => 'Nimal Perera',
        ]);
    }

    public function test_admission_number_must_be_unique(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        Student::factory()->create(['admission_number' => 'ADM-2026-001']);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.students.store'), $this->studentPayload())
            ->assertSessionHasErrors('admission_number');
    }

    public function test_super_admin_can_filter_and_update_student_information(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $student = Student::factory()->create([
            'full_name' => 'Amal Perera',
            'class' => '10',
            'section' => 'A',
        ]);
        $student->guardian()->create($this->guardianData());

        $this->actingAs($superAdmin)
            ->get(route('super_admin.students.index', [
                'search' => 'Amal',
                'class' => '10',
                'section' => 'A',
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Amal Perera');

        $payload = $this->studentPayload([
            'admission_number' => $student->admission_number,
            'full_name' => 'Amal Updated',
            'status' => 'transferred',
        ]);

        $this->actingAs($superAdmin)
            ->put(route('super_admin.students.update', $student), $payload)
            ->assertRedirect(route('super_admin.students.show', $student));

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'full_name' => 'Amal Updated',
            'status' => 'transferred',
        ]);
    }

    public function test_super_admin_can_manage_status_guardian_and_document_records(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $student = Student::factory()->create();

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.students.status.update', $student), ['status' => 'graduated'])
            ->assertRedirect();

        $this->actingAs($superAdmin)
            ->put(route('super_admin.students.guardians.update', $student), [
                'guardian_name' => 'Updated Guardian',
                'relationship' => 'Mother',
                'contact_number' => '0771234567',
                'email' => 'guardian@example.test',
                'address' => 'Colombo',
            ])
            ->assertRedirect();

        $this->actingAs($superAdmin)
            ->post(route('super_admin.students.documents.store', $student), [
                'document_name' => 'Birth Certificate Copy',
                'document_type' => 'Birth Certificate',
                'uploaded_date' => '2026-06-13',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'graduated']);
        $this->assertDatabaseHas('student_guardians', ['student_id' => $student->id, 'guardian_name' => 'Updated Guardian']);
        $this->assertDatabaseHas('student_documents', ['student_id' => $student->id, 'document_name' => 'Birth Certificate Copy']);
    }

    public function test_deleting_student_removes_related_records(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $student = Student::factory()->create();
        $student->guardian()->create($this->guardianData());
        $student->documents()->create([
            'document_name' => 'Identity Record',
            'document_type' => 'Identity',
            'uploaded_date' => '2026-06-13',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.students.destroy', $student))
            ->assertRedirect(route('super_admin.students.index'));

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('student_guardians', ['student_id' => $student->id]);
        $this->assertDatabaseMissing('student_documents', ['student_id' => $student->id]);
    }

    private function studentPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Amal Perera',
            'admission_number' => 'ADM-2026-001',
            'date_of_birth' => '2012-05-10',
            'gender' => 'male',
            'address' => 'Kandy',
            'contact_number' => '0711234567',
            'email' => 'amal@example.test',
            'class' => '10',
            'section' => 'A',
            'admission_date' => '2026-01-05',
            'status' => 'active',
            'guardian_name' => 'Nimal Perera',
            'relationship' => 'Father',
            'guardian_contact_number' => '0777654321',
            'guardian_email' => 'nimal@example.test',
            'guardian_address' => 'Kandy',
        ], $overrides);
    }

    private function guardianData(): array
    {
        return [
            'guardian_name' => 'Nimal Perera',
            'relationship' => 'Father',
            'contact_number' => '0777654321',
            'email' => 'nimal@example.test',
            'address' => 'Kandy',
        ];
    }
}
