<?php

namespace Database\Factories;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherSubjectAssignment>
 */
class TeacherSubjectAssignmentFactory extends Factory
{
    public function definition(): array
    {
        $schoolClass = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();

        ClassSubject::create([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        return [
            'teacher_id' => Teacher::factory(),
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ];
    }
}
