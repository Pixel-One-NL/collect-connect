<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Color;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartColor>
 */
class PartColorFactory extends Factory
{
    protected $model = PartColor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'part_id' => Part::factory(),
            'color_id' => Color::factory(),
        ];
    }
}
