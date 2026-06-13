<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admission_number' => 'ADM-'.fake()->unique()->numberBetween(1000, 9999),
            'full_name' => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'address' => fake()->address(),
            'contact_number' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'class' => (string) fake()->numberBetween(1, 13),
            'section' => fake()->randomElement(['A', 'B', 'C']),
            'admission_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
