<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentClassAssignment>
 */
class StudentClassAssignmentFactory extends Factory
{
    public function definition(): array
    {
        $schoolClass = SchoolClass::factory()->create();

        return [
            'student_id' => Student::factory(),
            'school_class_id' => $schoolClass->id,
            'school_section_id' => SchoolSection::factory()->create([
                'school_class_id' => $schoolClass->id,
            ])->id,
            'academic_year' => now()->year.'/'.(now()->year + 1),
            'status' => 'assigned',
            'assigned_at' => now(),
        ];
    }
}
