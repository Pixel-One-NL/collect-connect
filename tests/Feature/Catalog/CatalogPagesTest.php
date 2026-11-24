<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Models\Color;
use App\Models\Inventory;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Product;
use App\Models\Set;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_set_uses_latest_inventory_and_exact_color_and_preserves_unavailable_parts(): void
    {
        $set = Set::factory()->create();
        $old = Inventory::factory()->create(['set_id' => $set->id, 'version' => 1]);
        $latest = Inventory::factory()->create(['set_id' => $set->id, 'version' => 2]);
        $part = Part::factory()->create();
        $red = Color::factory()->create();
        $blue = Color::factory()->create();
        $product = Product::factory()->create(['productable_type' => $part->getMorphClass(), 'productable_id' => $part->id, 'color_id' => $red->id, 'stock' => 5]);
        DB::table('inventory_parts')->insert([
            ['inventory_id' => $old->id, 'part_id' => $part->id, 'color_id' => $red->id, 'quantity' => 99, 'is_spare' => false],
            ['inventory_id' => $latest->id, 'part_id' => $part->id, 'color_id' => $red->id, 'quantity' => 2, 'is_spare' => false],
            ['inventory_id' => $latest->id, 'part_id' => $part->id, 'color_id' => $blue->id, 'quantity' => 3, 'is_spare' => true],
        ]);
        $this->get(route('sets.show', $set))->assertOk()->assertInertia(fn ($page) => $page
            ->has('in_stock_parts', 1)->has('out_of_stock_parts', 1)
            ->where('in_stock_parts.0.id', $product->id)->where('in_stock_parts.0.quantity_in_set', 2)
            ->where('out_of_stock_parts.0.url', null)->where('out_of_stock_parts.0.quantity_in_set', 3)
            ->where('out_of_stock_parts.0.is_spare', true));
        $this->get(route('product.show', $product))->assertOk()->assertInertia(fn ($page) => $page->has('related_sets.data', 1)->where('related_sets.data.0.id', $set->id));
        $this->get(route('sets.show', [$set, 'color_id' => $blue->id]))->assertOk()->assertInertia(fn ($page) => $page->has('in_stock_parts', 0)->has('out_of_stock_parts', 1));
    }

    public function test_minifig_theme_filter_returns_minifigs_in_theme_and_its_children(): void
    {
        $starWars = Theme::factory()->create(['name' => 'Star Wars']);
        $clones = Theme::factory()->create(['name' => 'Clone Wars', 'parent_id' => $starWars->rebrickable_id]);
        $city = Theme::factory()->create(['name' => 'City']);

        $parentProduct = $this->minifigProductInTheme($starWars);
        $childProduct = $this->minifigProductInTheme($clones);
        $this->minifigProductInTheme($city);

        $this->get(route('catalog.minifigs', ['theme_id' => $starWars->rebrickable_id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('title', 'Star Wars')
                ->where('active.theme_id', $starWars->rebrickable_id)
                ->has('products.data', 2)
                ->where('products.data', fn ($products) => collect($products)->pluck('id')->sort()->values()->all()
                    === collect([$parentProduct->id, $childProduct->id])->sort()->values()->all()));

        $this->get(route('catalog.minifigs', ['theme_id' => $clones->rebrickable_id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.id', $childProduct->id));
    }

    public function test_minifig_theme_filters_only_list_themes_with_stock_and_their_parents(): void
    {
        $starWars = Theme::factory()->create(['name' => 'Star Wars']);
        $clones = Theme::factory()->create(['name' => 'Clone Wars', 'parent_id' => $starWars->rebrickable_id]);
        $outOfStock = Theme::factory()->create(['name' => 'Castle']);
        Theme::factory()->create(['name' => 'Unused']);

        $this->minifigProductInTheme($clones);
        $this->minifigProductInTheme($outOfStock, stock: 0);

        $this->get(route('catalog.minifigs'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('filters.themes', 2)
                ->where('filters.themes.0', ['id' => $clones->rebrickable_id, 'name' => 'Clone Wars', 'parent_id' => $starWars->rebrickable_id])
                ->where('filters.themes.1', ['id' => $starWars->rebrickable_id, 'name' => 'Star Wars', 'parent_id' => null]));

        $this->get(route('catalog.parts'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('filters.themes', 0));
    }

    private function minifigProductInTheme(Theme $theme, int $stock = 3): Product
    {
        $minifig = Minifig::factory()->create();
        $set = Set::factory()->create(['theme_id' => $theme->rebrickable_id]);
        $inventory = Inventory::factory()->create(['set_id' => $set->id]);
        DB::table('inventory_minifigs')->insert(['inventory_id' => $inventory->id, 'minifig_id' => $minifig->id, 'quantity' => 1]);

        return Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory()->create()->id,
            'stock' => $stock,
        ]);
    }
}
