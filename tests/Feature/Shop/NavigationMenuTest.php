<?php

declare(strict_types=1);

namespace Tests\Feature\Shop;

use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_parts_menu_lists_part_categories_with_purchasable_products(): void
    {
        $bricks = $this->categoryWithProduct('Bricks', ['stock' => 5, 'is_active' => true]);
        $plates = $this->categoryWithProduct('Plates', ['stock' => 3, 'is_active' => true]);

        $this->get(route('catalog.parts'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('navigation.0.id', 'parts')
                ->where('navigation.0.href', '/onderdelen')
                ->missing('navigation.0.source')
                ->has('navigation.0.children', 2)
                ->where('navigation.0.children.0', [
                    'id' => "parts-category-{$bricks->id}",
                    'label' => 'Bricks',
                    'href' => "/onderdelen?category_id={$bricks->id}",
                ])
                ->where('navigation.0.children.1.label', 'Plates')
                ->where('navigation.0.children.1.href', "/onderdelen?category_id={$plates->id}"));
    }

    public function test_parts_menu_hides_categories_without_purchasable_products(): void
    {
        PartCategory::factory()->create(['name' => 'Empty']);
        $this->categoryWithProduct('Out of stock', ['stock' => 0, 'is_active' => true]);
        $this->categoryWithProduct('Inactive', ['stock' => 5, 'is_active' => false]);

        $this->get(route('catalog.parts'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('navigation.0.children', 0));
    }

    public function test_items_without_source_have_no_children(): void
    {
        $this->get(route('catalog.parts'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('navigation.1.id', 'minifigs')
                ->missing('navigation.1.children'));
    }

    /**
     * @param  array<string, mixed>  $productAttributes
     */
    private function categoryWithProduct(string $name, array $productAttributes): PartCategory
    {
        $category = PartCategory::factory()->create(['name' => $name]);
        $part = Part::factory()->create(['part_category_id' => $category->id]);

        Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            ...$productAttributes,
        ]);

        return $category;
    }
}
