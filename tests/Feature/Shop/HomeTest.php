<?php

declare(strict_types=1);

namespace Tests\Feature\Shop;

use App\Models\Color;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Product;
use App\Models\TrendingProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_curated_trending_products_in_admin_order(): void
    {
        $first = $this->createProduct(stock: 1);
        $second = $this->createProduct(stock: 999);

        TrendingProduct::factory()->create(['product_id' => $second->id, 'sort_order' => 2]);
        TrendingProduct::factory()->create(['product_id' => $first->id, 'sort_order' => 1]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('index')
                ->has('trendingProducts.data', 2)
                ->where('trendingProducts.data.0.id', $first->id)
                ->where('trendingProducts.data.1.id', $second->id),
            );
    }

    public function test_homepage_falls_back_to_stocked_products_without_curation(): void
    {
        $this->createProduct(stock: 0);
        $stocked = $this->createProduct(stock: 25);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('trendingProducts.data', 1)
                ->where('trendingProducts.data.0.id', $stocked->id),
            );
    }

    public function test_homepage_lists_categories_that_have_products_in_stock(): void
    {
        $stockedCategory = PartCategory::factory()->create(['name' => 'Bricks']);
        $emptyCategory = PartCategory::factory()->create(['name' => 'Windows']);

        $this->createProduct(stock: 5, category: $stockedCategory);
        $this->createProduct(stock: 0, category: $emptyCategory);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('popularCategories', 1)
                ->where('popularCategories.0.id', $stockedCategory->id)
                ->where('popularCategories.0.name', 'Bricks'),
            );
    }

    private function createProduct(int $stock, ?PartCategory $category = null): Product
    {
        $part = Part::factory()->create([
            'part_category_id' => $category?->id ?? PartCategory::factory(),
        ]);

        return Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => $stock,
            'price' => 995,
        ]);
    }
}
