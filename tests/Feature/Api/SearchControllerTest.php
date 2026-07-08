<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Color;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_searches_products_by_name(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);
        $this->partWithColors('Blue Plate 1x2', '3023', [['color' => 'Blue', 'price' => 99, 'stock' => 4]]);

        $this->getJson('/api/search?q=red')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Red Brick 2x4')
            ->assertJsonPath('data.0.legoNumber', '3001')
            ->assertJsonPath('data.0.priceMin', 1.5)
            ->assertJsonPath('data.0.colors.0.stock', 10);
    }

    public function test_it_searches_products_by_bricklink_number(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);
        $this->partWithColors('Blue Plate 1x2', '3023', [['color' => 'Blue', 'price' => 99, 'stock' => 4]]);

        $this->getJson('/api/search?q=3023')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Blue Plate 1x2');
    }

    public function test_it_groups_color_variants_of_a_part_into_one_result(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [
            ['color' => 'Blue', 'price' => 150, 'stock' => 5],
            ['color' => 'Azure', 'price' => 200, 'stock' => 3],
        ]);

        $this->getJson('/api/search?q=brick')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.colors')
            // colors sorted by name asc -> "Azure" is primary.
            ->assertJsonPath('data.0.primaryColor.name', 'Azure')
            ->assertJsonPath('data.0.colors.0.name', 'Azure')
            ->assertJsonPath('data.0.colors.0.stock', 3)
            ->assertJsonPath('data.0.colors.1.name', 'Blue')
            // min/max across variants, cents -> euros.
            ->assertJsonPath('data.0.priceMin', 1.5)
            ->assertJsonPath('data.0.priceMax', 2);
    }

    public function test_it_returns_empty_for_a_blank_query(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);

        $this->getJson('/api/search?q=')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_it_returns_empty_when_nothing_matches(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);

        $this->getJson('/api/search?q=nonexistent')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /**
     * @param  list<array{color: string, price: int, stock: int}>  $variants
     */
    private function partWithColors(string $name, string $bricklinkId, array $variants): Part
    {
        $part = Part::factory()->create([
            'name' => $name,
            'bricklink_id' => $bricklinkId,
        ]);

        foreach ($variants as $variant) {
            $color = Color::factory()->create(['name' => $variant['color']]);

            Product::factory()->create([
                'productable_type' => $part->getMorphClass(),
                'productable_id' => $part->id,
                'color_id' => $color->id,
                'price' => $variant['price'],
                'stock' => $variant['stock'],
            ]);
        }

        return $part;
    }
}
