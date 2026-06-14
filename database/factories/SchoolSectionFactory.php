<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\SchoolSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolSection>
 */
class SchoolSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_class_id' => SchoolClass::factory(),
            'name' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'capacity' => 40,
            'status' => 'active',
        ];
    }
}
