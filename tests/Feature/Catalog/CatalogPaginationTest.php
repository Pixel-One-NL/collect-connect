<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Models\Color;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CatalogPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $color = Color::factory()->create();

        Part::factory()->count(49)->create()->each(fn (Part $part) => Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $color->id,
            'stock' => 5,
        ]));
    }

    public function test_pagination_labels_are_translated_in_dutch_without_an_english_fallback(): void
    {
        App::setLocale('nl');
        config(['app.fallback_locale' => 'nl']);

        $this->get(route('catalog.parts'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.meta.last_page', 2)
                ->where('products.meta.links.0.label', '&laquo; Vorige')
                ->where('products.meta.links.3.label', 'Volgende &raquo;'));
    }

    public function test_the_second_page_renders_and_keeps_the_filters(): void
    {
        $colorId = Product::query()->value('color_id');

        $this->get(route('catalog.parts', ['color_id' => $colorId, 'page' => 2]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.meta.current_page', 2)
                ->has('products.data', 1)
                ->where('products.meta.links.0.url', fn (string $url): bool => str_contains($url, "color_id={$colorId}")));
    }
}
