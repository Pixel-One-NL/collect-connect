<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Models\Minifig;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Support\ImageUrl;
use App\Support\ImageVariant;
use Tests\TestCase;

class ImageUrlTest extends TestCase
{
    public function test_it_builds_delivery_url_per_variant(): void
    {
        $this->assertSame('https://imagedelivery.net/test-hash/img-1/thumb', ImageUrl::fromId('img-1', ImageVariant::Thumb));
        $this->assertSame('https://imagedelivery.net/test-hash/img-1/medium', ImageUrl::fromId('img-1', ImageVariant::Medium));
        $this->assertSame('https://imagedelivery.net/test-hash/img-1/large', ImageUrl::fromId('img-1'));
    }

    public function test_delivery_host_comes_from_config(): void
    {
        config(['cloudflare.delivery_url' => 'https://example.test/cdn-cgi/imagedelivery/']);

        $this->assertSame('https://example.test/cdn-cgi/imagedelivery/test-hash/img-1/large', ImageUrl::fromId('img-1'));
    }

    public function test_it_returns_null_without_image_id(): void
    {
        $this->assertNull(ImageUrl::fromId(null));
        $this->assertNull(ImageUrl::fromId(''));
        $this->assertNull(ImageUrl::forMinifig(new Minifig));
    }

    public function test_for_part_only_uses_the_exact_color(): void
    {
        $part = $this->partWithColors([1 => null, 2 => 'img-2']);

        $this->assertNull(ImageUrl::forPart($part, 1));
        $this->assertNull(ImageUrl::forPart($part, 99));
        $this->assertSame('https://imagedelivery.net/test-hash/img-2/large', ImageUrl::forPart($part, 2));
    }

    public function test_for_part_any_color_prefers_exact_color_then_falls_back(): void
    {
        $part = $this->partWithColors([1 => 'img-1', 2 => 'img-2', 3 => null]);

        $this->assertSame('https://imagedelivery.net/test-hash/img-2/large', ImageUrl::forPartAnyColor($part, 2));
        $this->assertSame('https://imagedelivery.net/test-hash/img-1/large', ImageUrl::forPartAnyColor($part, 3));
        $this->assertNull(ImageUrl::forPartAnyColor($this->partWithColors([1 => null]), 1));
    }

    /**
     * @param  array<int, string|null>  $imageIdsByColorId
     */
    private function partWithColors(array $imageIdsByColorId): Part
    {
        $partColors = collect($imageIdsByColorId)->map(function (?string $imageId, int $colorId): PartColor {
            $partColor = new PartColor;
            $partColor->color_id = $colorId;
            $partColor->cloudflare_image_id = $imageId;

            return $partColor;
        })->values();

        $part = new Part;
        $part->setRelation('partColors', new \Illuminate\Database\Eloquent\Collection($partColors->all()));

        return $part;
    }
}
