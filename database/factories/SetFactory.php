<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Set;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Set>
 */
class SetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'set_num' => fake()->unique()->numerify('####-#'),
            'name' => fake()->words(3, asText: true),
            'year' => fake()->numberBetween(1970, 2025),
            'theme_id' => fake()->numberBetween(1, 500),
            'num_parts' => fake()->numberBetween(0, 5000),
            'img_url' => null,
        ];
    }
}
