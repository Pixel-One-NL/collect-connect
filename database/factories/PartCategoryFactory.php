<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PartCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartCategory>
 */
class PartCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rebrickable_id' => fake()->unique()->numerify(),
            'name' => fake()->words(asText: true),
        ];
    }
}
