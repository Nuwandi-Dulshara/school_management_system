<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Mathematics',
            'Science',
            'English',
            'History',
            'Geography',
            'Information Technology',
        ]);

        return [
            'code' => strtoupper(fake()->unique()->bothify('SUB-###')),
            'name' => $name,
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
