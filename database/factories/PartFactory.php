<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Part>
 */
class PartFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'part_category_id' => PartCategory::factory(),
            'rebrickable_id' => fake()->unique()->numerify(),
            'bricklink_id' => fake()->unique()->numerify(),
            'ldraw_id' => null,
            'rebrickable_img_url' => null,
            'bricqer_img_url' => null,
            'name' => fake()->words(asText: true),
        ];
    }
}
