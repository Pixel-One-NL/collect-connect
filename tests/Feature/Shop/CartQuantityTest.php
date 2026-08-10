<?php

declare(strict_types=1);

namespace Tests\Feature\Shop;

use App\Models\Color;
use App\Models\Part;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_add_more_than_a_hundred_of_a_product(): void
    {
        $product = $this->productWithStock(500);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 250,
        ])->assertRedirect();

        $this->assertSame(250, app(CartService::class)->getItems()->firstOrFail()['quantity']);
    }

    public function test_cart_quantity_is_still_capped_at_the_real_stock(): void
    {
        $product = $this->productWithStock(150);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 400,
        ])->assertRedirect();

        $this->assertSame(150, app(CartService::class)->getItems()->firstOrFail()['quantity']);
    }

    public function test_the_product_page_exposes_the_real_stock_instead_of_a_capped_value(): void
    {
        $product = $this->productWithStock(342);

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/product')
                ->where('product.data.stock', 342)
                ->where('product.data.attributes', fn (Collection $attributes): bool => $attributes
                    ->firstWhere('label', 'Voorraad')['value'] === '342'));
    }

    private function productWithStock(int $stock): Product
    {
        $part = Part::factory()->create([
            'name' => 'Brick 2x4',
            'bricklink_id' => '3001',
        ]);

        return Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => $stock,
            'price' => 150,
        ]);
    }
}
