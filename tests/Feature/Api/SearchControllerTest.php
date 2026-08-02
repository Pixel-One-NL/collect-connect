<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Color;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Product;
use App\Models\Set;
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
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'Red Brick 2x4')
            ->assertJsonPath('data.products.0.lego_number', '3001')
            ->assertJsonPath('data.products.0.priceMin', 150)
            ->assertJsonPath('data.products.0.sibling_colors.0.stock', 10);
    }

    public function test_it_searches_products_by_bricklink_number(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);
        $this->partWithColors('Blue Plate 1x2', '3023', [['color' => 'Blue', 'price' => 99, 'stock' => 4]]);

        $this->getJson('/api/search?q=3023')
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'Blue Plate 1x2');
    }

    public function test_it_groups_color_variants_of_a_part_into_one_result(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [
            ['color' => 'Blue', 'price' => 150, 'stock' => 5],
            ['color' => 'Azure', 'price' => 200, 'stock' => 3],
        ]);

        $this->getJson('/api/search?q=brick')
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonCount(2, 'data.products.0.sibling_colors')
            ->assertJsonPath('data.products.0.sibling_colors.0.color.name', 'Azure')
            ->assertJsonPath('data.products.0.sibling_colors.0.stock', 3)
            ->assertJsonPath('data.products.0.sibling_colors.1.color.name', 'Blue')
            ->assertJsonPath('data.products.0.priceMin', 150)
            ->assertJsonPath('data.products.0.priceMax', 200);
    }

    public function test_it_searches_minifigs_by_name_and_bricklink_id(): void
    {
        $luke = Minifig::factory()->create([
            'name' => 'Luke Skywalker',
            'bricklink_id' => 'sw0001',
            'rebrickable_id' => 'fig-000100',
        ]);
        $han = Minifig::factory()->create([
            'name' => 'Han Solo',
            'bricklink_id' => 'sw0002',
        ]);
        Product::factory()->create([
            'productable_type' => $luke->getMorphClass(),
            'productable_id' => $luke->id,
            'color_id' => Color::factory(),
            'stock' => 2,
            'price' => 1000,
        ]);
        Product::factory()->create([
            'productable_type' => $han->getMorphClass(),
            'productable_id' => $han->id,
            'color_id' => Color::factory(),
            'stock' => 1,
            'price' => 900,
        ]);

        $this->getJson('/api/search?q=luke')
            ->assertOk()
            ->assertJsonCount(1, 'data.minifigs')
            ->assertJsonPath('data.minifigs.0.title', 'Luke Skywalker')
            ->assertJsonPath('data.minifigs.0.lego_number', 'sw0001')
            ->assertJsonPath('data.minifigs.0.type', 'minifig');

        $this->getJson('/api/search?q=sw0002')
            ->assertOk()
            ->assertJsonCount(1, 'data.minifigs')
            ->assertJsonPath('data.minifigs.0.title', 'Han Solo');
    }

    public function test_it_links_minifig_results_to_their_product_when_available(): void
    {
        $minifig = Minifig::factory()->create([
            'name' => 'Darth Vader',
            'bricklink_id' => 'sw0003',
        ]);
        $product = Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
            'price' => 1250,
            'stock' => 4,
        ]);

        $this->getJson('/api/search?q=vader')
            ->assertOk()
            ->assertJsonCount(1, 'data.minifigs')
            ->assertJsonPath('data.minifigs.0.priceMin', 1250)
            ->assertJsonPath('data.minifigs.0.id', $product->id)
            ->assertJsonPath('data.minifigs.0.url', route('product.show', $product, absolute: false));
    }

    public function test_it_hides_minifigs_without_in_stock_products(): void
    {
        $minifig = Minifig::factory()->create([
            'name' => 'Out Of Stock Dude',
            'bricklink_id' => 'sw0099',
        ]);
        Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
            'stock' => 0,
            'price' => 500,
        ]);

        $this->getJson('/api/search?q=dude')
            ->assertOk()
            ->assertJsonCount(0, 'data.minifigs');
    }

    public function test_minifig_search_images_use_media_library_not_bricqer_cdn(): void
    {
        $minifig = Minifig::factory()->create([
            'name' => 'Media Fig',
            'bricklink_id' => 'sw0100',
        ]);
        $media = $minifig
            ->addMediaFromString(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==') ?: 'x')
            ->usingFileName('fig.png')
            ->toMediaCollection(Minifig::BRICQER_IMAGE_COLLECTION);

        Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
            'stock' => 3,
            'price' => 800,
        ]);

        $response = $this->getJson('/api/search?q=media');
        $response->assertOk()->assertJsonCount(1, 'data.minifigs');

        $image = $response->json('data.minifigs.0.image');
        $this->assertNotNull($image);
        $this->assertStringStartsWith('/', $image);
        $this->assertStringContainsString((string) $media->id, $image);
        $this->assertStringNotContainsString('cdn.bricqer.com', (string) $image);
    }

    public function test_it_keeps_part_products_and_minifigs_in_separate_buckets(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);

        $minifig = Minifig::factory()->create([
            'name' => 'Red Soldier',
            'bricklink_id' => 'cas001',
        ]);
        Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
            'price' => 500,
            'stock' => 2,
        ]);

        $this->getJson('/api/search?q=red')
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonCount(1, 'data.minifigs')
            ->assertJsonPath('data.products.0.title', 'Red Brick 2x4')
            ->assertJsonPath('data.minifigs.0.title', 'Red Soldier');
    }

    public function test_it_returns_sets_alongside_products_and_minifigs(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);
        $minifig = Minifig::factory()->create(['name' => 'Red Guard', 'bricklink_id' => 'cas002']);
        Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
            'stock' => 2,
            'price' => 400,
        ]);
        Set::factory()->create(['name' => 'Red Castle', 'set_num' => '375-1']);

        $this->getJson('/api/search?q=red')
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonCount(1, 'data.minifigs')
            ->assertJsonCount(1, 'data.sets')
            ->assertJsonPath('data.sets.0.name', 'Red Castle');
    }

    public function test_it_returns_empty_for_a_blank_query(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);
        Minifig::factory()->create(['name' => 'Luke', 'bricklink_id' => 'sw0001']);

        $this->getJson('/api/search?q=')
            ->assertOk()
            ->assertJsonPath('data.products', [])
            ->assertJsonPath('data.minifigs', [])
            ->assertJsonPath('data.sets', []);
    }

    public function test_it_returns_empty_when_nothing_matches(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 10]]);
        Minifig::factory()->create(['name' => 'Luke', 'bricklink_id' => 'sw0001']);

        $this->getJson('/api/search?q=nonexistent')
            ->assertOk()
            ->assertJsonPath('data.products', [])
            ->assertJsonPath('data.minifigs', [])
            ->assertJsonPath('data.sets', []);
    }

    public function test_it_excludes_out_of_stock_products_from_search(): void
    {
        $this->partWithColors('Red Brick 2x4', '3001', [['color' => 'Red', 'price' => 150, 'stock' => 0]]);
        $this->partWithColors('Blue Plate 1x2', '3023', [['color' => 'Blue', 'price' => 99, 'stock' => 4]]);

        $this->getJson('/api/search?q=red')
            ->assertOk()
            ->assertJsonCount(0, 'data.products');

        $this->getJson('/api/search?q=blue')
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'Blue Plate 1x2');
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
