<?php

declare(strict_types=1);

namespace Tests\Feature\Shop;

use App\Models\Color;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\PartCategory;
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
                ->where('product.data.lego_number', 'sw0001')
                ->where('product.data.category_link', ['label' => 'Minifiguren', 'url' => route('catalog.minifigs', absolute: false)]));
    }

    public function test_a_part_links_to_its_category_collection_page(): void
    {
        $category = PartCategory::factory()->create(['name' => 'Bricks']);
        $part = Part::factory()->create(['part_category_id' => $category->id]);
        $product = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => 5,
        ]);

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('product.data.category_link.label', 'Bricks')
                ->where('product.data.category_link.url', route('catalog.parts', ['category_id' => $category->id], absolute: false)));
    }

    public function test_every_color_variant_is_listed_with_stock_price_and_purchasability(): void
    {
        $part = Part::factory()->create();
        $current = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => 5,
            'price' => 100,
        ]);
        $soldOut = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => 0,
            'price' => 200,
        ]);
        $inactive = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => 9,
            'price' => 300,
            'is_active' => false,
        ]);

        $this->get(route('product.show', $current))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('product.data.sibling_colors', 3)
                ->where('product.data.sibling_colors', fn ($siblings): bool => collect($siblings)->keyBy('id')->map(fn (array $sibling): array => [
                    $sibling['stock'], $sibling['price'], $sibling['purchasable'],
                ])->all() === [
                    $current->id => [5, 100, true],
                    $soldOut->id => [0, 200, false],
                    $inactive->id => [0, 300, false],
                ]));
    }
}
