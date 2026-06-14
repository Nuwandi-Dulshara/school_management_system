<?php

namespace Tests\Feature;

use App\Models\FeeAssignment;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeesManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_fee_types(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('fees.types.store'), [
                'name' => 'Tuition Fee',
                'description' => 'Monthly tuition payment',
                'amount' => 2500,
                'frequency' => 'monthly',
                'status' => 'active',
            ])
            ->assertRedirect(route('fees.types.index'))
            ->assertSessionHas('success');

        $feeType = FeeType::firstOrFail();

        $this->actingAs($admin)
            ->get(route('fees.types.index', ['search' => 'Tuition']))
            ->assertOk()
            ->assertSee('Tuition Fee');

        $this->actingAs($admin)
            ->put(route('fees.types.update', $feeType), [
                'name' => 'Updated Tuition Fee',
                'description' => null,
                'amount' => 3000,
                'frequency' => 'termly',
                'status' => 'active',
            ])
            ->assertRedirect(route('fees.types.index'));

        $this->assertDatabaseHas('fee_types', ['id' => $feeType->id, 'name' => 'Updated Tuition Fee', 'amount' => 3000]);
    }

    public function test_class_fee_assignment_creates_one_record_per_student_without_duplicates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class, $section] = $this->classSection();
        $first = $this->student($class, $section, 'ADM-001', 'First Student');
        $second = $this->student($class, $section, 'ADM-002', 'Second Student');
        $feeType = $this->feeType();
        $payload = $this->assignmentPayload($class, $section, $feeType);

        $this->actingAs($admin)->post(route('fees.assignments.store'), $payload)->assertRedirect(route('fees.assignments.index'));

        $this->assertDatabaseCount('fee_assignments', 2);
        $this->assertDatabaseHas('fee_assignments', ['student_id' => $first->id, 'balance_amount' => 2500, 'payment_status' => 'unpaid']);
        $this->assertDatabaseHas('fee_assignments', ['student_id' => $second->id, 'balance_amount' => 2500]);

        $this->actingAs($admin)->post(route('fees.assignments.store'), $payload)->assertRedirect(route('fees.assignments.index'));
        $this->assertDatabaseCount('fee_assignments', 2);
    }

    public function test_payment_generates_receipt_and_updates_balance_and_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $assignment = $this->assignment();

        $this->actingAs($admin)
            ->post(route('fees.payments.store', $assignment), [
                'payment_amount' => 1000,
                'payment_date' => '2026-06-14',
                'payment_method' => 'cash',
                'reference_number' => 'CASH-100',
                'remarks' => 'First payment',
            ])
            ->assertRedirect();

        $payment = FeePayment::firstOrFail();
        $this->assertStringStartsWith('FEE-', $payment->receipt_number);
        $this->assertDatabaseHas('fee_assignments', [
            'id' => $assignment->id,
            'paid_amount' => 1000,
            'balance_amount' => 1500,
            'payment_status' => 'partially_paid',
        ]);

        $this->actingAs($admin)
            ->get(route('fees.receipt', $payment))
            ->assertOk()
            ->assertSee($payment->receipt_number)
            ->assertSee('First payment');

        $this->actingAs($admin)
            ->post(route('fees.payments.store', $assignment->fresh()), [
                'payment_amount' => 1500,
                'payment_date' => '2026-06-15',
                'payment_method' => 'bank_transfer',
                'reference_number' => 'BANK-200',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('fee_assignments', [
            'id' => $assignment->id,
            'paid_amount' => 2500,
            'balance_amount' => 0,
            'payment_status' => 'paid',
        ]);
    }

    public function test_payment_cannot_exceed_balance_and_edit_delete_recalculate_assignment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $assignment = $this->assignment();

        $this->actingAs($admin)
            ->post(route('fees.payments.store', $assignment), [
                'payment_amount' => 3000,
                'payment_date' => '2026-06-14',
                'payment_method' => 'cash',
            ])
            ->assertSessionHasErrors('payment_amount');

        $this->actingAs($admin)->post(route('fees.payments.store', $assignment), [
            'payment_amount' => 500,
            'payment_date' => '2026-06-14',
            'payment_method' => 'card',
        ]);
        $payment = FeePayment::firstOrFail();

        $this->actingAs($admin)
            ->put(route('fees.payments.update', $payment), [
                'payment_amount' => 750,
                'payment_date' => '2026-06-14',
                'payment_method' => 'online',
                'reference_number' => 'WEB-1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('fee_assignments', ['id' => $assignment->id, 'paid_amount' => 750, 'balance_amount' => 1750]);

        $this->actingAs($admin)->delete(route('fees.payments.destroy', $payment))->assertRedirect(route('fees.payments.index'));
        $this->assertDatabaseHas('fee_assignments', ['id' => $assignment->id, 'paid_amount' => 0, 'balance_amount' => 2500]);
    }

    public function test_pending_page_marks_past_due_assignment_overdue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $assignment = $this->assignment(['due_date' => today()->subDay()->toDateString()]);

        $this->actingAs($admin)
            ->get(route('fees.pending'))
            ->assertOk()
            ->assertSee($assignment->student->full_name)
            ->assertSee('Overdue');

        $this->assertDatabaseHas('fee_assignments', ['id' => $assignment->id, 'payment_status' => 'overdue']);
    }

    public function test_students_see_only_own_fees_and_teachers_cannot_manage_fees(): void
    {
        [$class, $section] = $this->classSection();
        $studentUser = User::factory()->create(['role' => 'student', 'email' => 'linked@student.test']);
        $student = $this->student($class, $section, 'ADM-100', 'Linked Student', 'linked@student.test');
        $other = $this->student($class, $section, 'ADM-200', 'Other Student', 'other@student.test');
        $ownAssignment = $this->assignment([], $student);
        $this->assignment([], $other);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('fees.payments.store', $ownAssignment), [
            'payment_amount' => 500,
            'payment_date' => '2026-06-14',
            'payment_method' => 'cash',
        ]);
        $ownPayment = FeePayment::firstOrFail();

        $this->actingAs($studentUser)
            ->get(route('fees.assignments.index'))
            ->assertOk()
            ->assertSee('Linked Student')
            ->assertDontSee('Other Student');

        $this->actingAs($studentUser)
            ->get(route('fees.payments.index'))
            ->assertOk()
            ->assertSee($ownPayment->receipt_number);

        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher)->get(route('fees.types.index'))->assertForbidden();
        $this->actingAs($studentUser)->get(route('fees.assign'))->assertForbidden();
    }

    private function classSection(): array
    {
        $class = SchoolClass::factory()->create(['name' => 'Grade 10']);
        $section = SchoolSection::factory()->create(['school_class_id' => $class->id, 'name' => 'A']);

        return [$class, $section];
    }

    private function student(
        SchoolClass $class,
        SchoolSection $section,
        string $admission = 'ADM-001',
        string $name = 'Test Student',
        string $email = 'student@test.local'
    ): Student {
        return Student::factory()->create([
            'admission_number' => $admission,
            'full_name' => $name,
            'email' => $email,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'class' => $class->name,
            'section' => $section->name,
        ]);
    }

    private function feeType(): FeeType
    {
        return FeeType::create([
            'name' => 'Tuition Fee '.str()->random(4),
            'description' => 'School tuition',
            'amount' => 2500,
            'frequency' => 'monthly',
            'status' => 'active',
        ]);
    }

    private function assignmentPayload(SchoolClass $class, SchoolSection $section, FeeType $feeType): array
    {
        return [
            'academic_year' => '2026/2027',
            'fee_type_id' => $feeType->id,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'assigned_amount' => 2500,
            'due_date' => today()->addMonth()->toDateString(),
            'status' => 'active',
        ];
    }

    private function assignment(array $overrides = [], ?Student $student = null): FeeAssignment
    {
        if (! $student) {
            [$class, $section] = $this->classSection();
            $student = $this->student($class, $section);
        } else {
            $class = $student->schoolClass;
            $section = $student->schoolSection;
        }

        return FeeAssignment::create(array_merge([
            'academic_year' => '2026/2027',
            'fee_type_id' => $this->feeType()->id,
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'student_id' => $student->id,
            'assigned_amount' => 2500,
            'paid_amount' => 0,
            'balance_amount' => 2500,
            'due_date' => today()->addMonth()->toDateString(),
            'payment_status' => 'unpaid',
            'status' => 'active',
        ], $overrides));
    }
}
