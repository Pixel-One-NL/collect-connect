<?php

declare(strict_types=1);

namespace Tests\Feature\Shop;

use App\Models\Color;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_a_part_product_page(): void
    {
        $part = Part::factory()->create([
            'name' => 'Brick 2x4',
            'bricklink_id' => '3001',
        ]);
        $product = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => 5,
            'price' => 150,
        ]);

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/product')
                ->has('product')
                ->where('product.data.id', $product->id)
                ->where('product.data.title', 'Brick 2x4')
                ->where('product.data.type', 'part'));
    }

    public function test_it_shows_a_minifig_product_page_without_part_colors_relationship_error(): void
    {
        $minifig = Minifig::factory()->create([
            'name' => 'Luke Skywalker',
            'bricklink_id' => 'sw0001',
        ]);
        $product = Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
            'stock' => 3,
            'price' => 1250,
        ]);

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/product')
                ->has('product')
                ->where('product.data.id', $product->id)
                ->where('product.data.title', 'Luke Skywalker')
                ->where('product.data.type', 'minifig')
                ->where('product.data.lego_number', 'sw0001'));
    }
}
