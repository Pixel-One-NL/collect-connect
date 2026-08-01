<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Color;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchableTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_searchable_array_works_for_minifig_products(): void
    {
        $minifig = Minifig::factory()->create([
            'name' => 'Luke Skywalker',
            'bricklink_id' => 'sw0001',
        ]);

        $product = Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
            'stock' => 5,
            'price' => 250,
        ]);

        $document = $product->toSearchableArray();

        $this->assertSame((string) $product->id, $document['id']);
        $this->assertSame('Luke Skywalker', $document['name']);
        $this->assertSame('sw0001', $document['bricklink_id']);
        $this->assertSame('minifig', $document['type']);
        $this->assertSame(0, $document['category_id']);
        $this->assertSame('', $document['category_name']);
        $this->assertSame(5, $document['stock']);
        $this->assertSame(250, $document['price']);
    }

    public function test_to_searchable_array_includes_part_category(): void
    {
        $category = PartCategory::factory()->create(['name' => 'Bricks']);
        $part = Part::factory()->create([
            'name' => 'Brick 2x4',
            'bricklink_id' => '3001',
            'part_category_id' => $category->id,
        ]);

        $product = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => 10,
            'price' => 15,
        ]);

        $document = $product->toSearchableArray();

        $this->assertSame('part', $document['type']);
        $this->assertSame($category->id, $document['category_id']);
        $this->assertSame('Bricks', $document['category_name']);
        $this->assertSame('Brick 2x4', $document['name']);
        $this->assertSame('3001', $document['bricklink_id']);
    }

    public function test_make_all_searchable_using_eager_loads_part_category_only_for_parts(): void
    {
        $category = PartCategory::factory()->create(['name' => 'Plates']);
        $part = Part::factory()->create([
            'name' => 'Plate 1x2',
            'part_category_id' => $category->id,
        ]);
        $minifig = Minifig::factory()->create(['name' => 'Han Solo']);

        Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
        ]);
        Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
        ]);

        $products = (new Product)->makeAllSearchableUsing(Product::query())->get();

        $this->assertCount(2, $products);

        foreach ($products as $product) {
            $this->assertNotEmpty($product->toSearchableArray());
        }
    }
}
