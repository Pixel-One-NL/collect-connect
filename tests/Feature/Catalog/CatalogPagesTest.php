<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Models\Color;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Product;
use App\Models\Set;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_parts_catalog_page_renders(): void
    {
        $part = Part::factory()->create();
        $color = Color::factory()->create();
        Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $color->id,
            'stock' => 5,
        ]);

        $this->get(route('catalog.parts'))->assertOk();
    }

    public function test_minifigs_catalog_page_renders(): void
    {
        $minifig = Minifig::factory()->create();
        $color = Color::factory()->create(['bricklink_color_id' => '0']);
        Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => $color->id,
            'stock' => 2,
        ]);

        $this->get(route('catalog.minifigs'))->assertOk();
    }

    public function test_sets_index_renders(): void
    {
        Set::factory()->create();

        $this->get(route('sets.index'))->assertOk();
    }

    public function test_sitemap_is_public(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_catalog_search_scopes_name_or_bricklink_to_the_productable(): void
    {
        $matching = Part::factory()->create([
            'name' => 'Blue Plate',
            'bricklink_id' => '3023',
        ]);
        $unrelated = Part::factory()->create([
            'name' => 'Red Brick',
            'bricklink_id' => '3001',
        ]);
        $color = Color::factory()->create();

        $matchProduct = Product::factory()->create([
            'productable_type' => $matching->getMorphClass(),
            'productable_id' => $matching->id,
            'color_id' => $color->id,
            'stock' => 5,
        ]);
        Product::factory()->create([
            'productable_type' => $unrelated->getMorphClass(),
            'productable_id' => $unrelated->id,
            'color_id' => $color->id,
            'stock' => 5,
        ]);

        $this->get(route('catalog.search', ['q' => '3023']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('shop/catalog')
                ->has('products.data', 1)
                ->where('products.data.0.id', $matchProduct->id));
    }
}
