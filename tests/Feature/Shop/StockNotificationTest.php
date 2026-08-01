<?php

declare(strict_types=1);

namespace Tests\Feature\Shop;

use App\Models\Color;
use App\Models\Part;
use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_register_for_restock_email(): void
    {
        $part = Part::factory()->create();
        $color = Color::factory()->create();
        $product = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $color->id,
            'stock' => 0,
        ]);

        $this->post(route('products.stock-notifications.store', $product), [
            'email' => 'buyer@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas(StockNotification::class, [
            'product_id' => $product->id,
            'email' => 'buyer@example.com',
        ]);
    }
}
