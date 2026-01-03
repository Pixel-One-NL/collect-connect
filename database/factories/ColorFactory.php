<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Color>
 */
class ColorFactory extends Factory
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
            'name' => fake()->words(asText: true),
            'hex' => str(fake()->hexColor())->remove('#')->toString(),
            'is_transparent' => fake()->boolean(0.1),
        ];
    }
}
