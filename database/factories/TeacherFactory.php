<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_number' => 'TCH-'.fake()->unique()->numberBetween(1000, 9999),
            'full_name' => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-65 years', '-22 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'address' => fake()->address(),
            'contact_number' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'qualification' => fake()->randomElement(['B.Ed.', 'B.Sc.', 'M.Ed.', 'M.Sc.']),
            'experience' => fake()->numberBetween(0, 35),
            'main_subject' => fake()->randomElement(['Mathematics', 'Science', 'English', 'History']),
            'joining_date' => fake()->dateTimeBetween('-15 years', 'now')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
