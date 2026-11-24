<?php

declare(strict_types=1);

namespace Tests\Feature\Shop;

use App\Models\Color;
use App\Models\Part;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Support\SessionKey;
use Tests\TestCase;

class CartFlashMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_product_flashes_a_success_toast(): void
    {
        $product = $this->product(['stock' => 10, 'commerce_title' => 'Brick 2x4 Rood']);

        $this->from(route('product.show', $product))
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect(route('product.show', $product))
            ->assertSessionHas(SessionKey::FLASH_DATA.'.toast', fn (array $toast): bool => $toast['type'] === 'success'
                && $toast['title'] === 'Toegevoegd aan je winkelwagen'
                && $toast['message'] === 'Brick 2x4 Rood'
                && $toast['action']['href'] === route('cart.show', absolute: false));

        $this->assertSame(2, app(CartService::class)->getItems()->firstOrFail()['quantity']);
    }

    public function test_adding_an_unavailable_product_flashes_an_error_toast(): void
    {
        $product = $this->product(['stock' => 0]);

        $this->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertRedirect()
            ->assertSessionHas(SessionKey::FLASH_DATA.'.toast.type', 'error');

        $this->assertCount(0, app(CartService::class)->getItems());
    }

    public function test_the_toast_reaches_the_next_inertia_page_once(): void
    {
        $product = $this->product(['stock' => 5]);

        $this->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1]);

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->hasFlash('toast.type', 'success'));

        $this->get(route('product.show', $product))
            ->assertInertia(fn ($page) => $page->missingFlash('toast'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function product(array $attributes): Product
    {
        $part = Part::factory()->create(['name' => 'Brick 2x4']);

        return Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'price' => 150,
            ...$attributes,
        ]);
    }
}
