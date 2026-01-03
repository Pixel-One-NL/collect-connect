<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Minifig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Minifig>
 */
class MinifigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rebrickable_id' => fake()->unique()->numerify(),
            'bricklink_id' => fake()->unique()->numerify(),
            'name' => fake()->words(asText: true),
        ];
    }
}
