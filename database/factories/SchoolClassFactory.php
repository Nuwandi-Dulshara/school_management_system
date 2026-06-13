<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Grade '.fake()->unique()->numberBetween(1, 13),
            'capacity' => 120,
            'status' => 'active',
        ];
    }
}
