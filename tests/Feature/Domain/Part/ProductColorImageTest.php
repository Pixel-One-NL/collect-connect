<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Part;

use App\Http\Resources\Product\ProductResource;
use App\Models\Color;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductColorImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_resource_returns_the_cloudflare_image_for_its_own_color(): void
    {
        $part = Part::factory()->create();
        $red = Color::factory()->create(['name' => 'Red']);
        $blue = Color::factory()->create(['name' => 'Blue']);

        $this->attachImage($part, $red, 'testing-part-color-1');
        $this->attachImage($part, $blue, 'testing-part-color-2');

        $redProduct = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $red->id,
        ]);
        $blueProduct = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $blue->id,
        ]);

        $redProduct->load(['productable.partColors', 'color']);
        $blueProduct->load(['productable.partColors', 'color']);

        $request = Request::create('/');

        $redData = (new ProductResource($redProduct))->toArray($request);
        $blueData = (new ProductResource($blueProduct))->toArray($request);

        $this->assertSame('https://imagedelivery.net/test-hash/testing-part-color-1/large', $redData['image']);
        $this->assertSame('https://imagedelivery.net/test-hash/testing-part-color-2/large', $blueData['image']);
    }

    public function test_product_resource_does_not_fall_back_to_bricqer_cdn_for_parts(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);
        $color = Color::factory()->create([
            'name' => 'Red',
            'bricklink_color_id' => '5',
        ]);

        $product = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $color->id,
        ]);

        $product->load(['productable.partColors', 'color']);

        $data = (new ProductResource($product))->toArray(Request::create('/'));

        $this->assertNull($data['image']);
    }

    private function attachImage(Part $part, Color $color, string $imageId): void
    {
        PartColor::factory()->create([
            'part_id' => $part->id,
            'color_id' => $color->id,
            'cloudflare_image_id' => $imageId,
        ]);
    }
}
