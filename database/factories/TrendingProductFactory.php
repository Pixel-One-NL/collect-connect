<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\TrendingProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrendingProduct>
 */
class TrendingProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sort_order' => 0,
        ];
    }
}
