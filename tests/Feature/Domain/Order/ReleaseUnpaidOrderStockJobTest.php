<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Order;

use App\Domain\Order\Jobs\ReleaseUnpaidOrderStockJob;
use App\Models\Color;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseUnpaidOrderStockJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_releases_stock_for_expired_unpaid_reservations(): void
    {
        config(['orders.reservation_ttl_minutes' => 60]);

        $product = $this->makeProduct(stock: 2);

        $order = $this->makePendingOrder(reservedAt: now()->subHours(2));
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'title' => 'Brick',
            'unit_price_cents' => 100,
            'quantity' => 3,
        ]);

        $stats = (new ReleaseUnpaidOrderStockJob)->handle();

        $this->assertSame(1, $stats['released']);
        $this->assertSame(5, $product->refresh()->stock);
        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertNull($order->stock_reserved_at);
        $this->assertSame('unpaid_reservation_expired', $order->meta['stock_release_reason'] ?? null);
    }

    public function test_it_does_not_release_fresh_or_paid_orders(): void
    {
        config(['orders.reservation_ttl_minutes' => 60]);

        $product = $this->makeProduct(stock: 1);

        $fresh = $this->makePendingOrder(reservedAt: now()->subMinutes(10));
        OrderItem::query()->create([
            'order_id' => $fresh->id,
            'product_id' => $product->id,
            'title' => 'Fresh',
            'unit_price_cents' => 100,
            'quantity' => 1,
        ]);

        $paid = $this->makePendingOrder(reservedAt: now()->subHours(3));
        $paid->forceFill(['status' => 'paid', 'paid_at' => now()])->save();
        OrderItem::query()->create([
            'order_id' => $paid->id,
            'product_id' => $product->id,
            'title' => 'Paid',
            'unit_price_cents' => 100,
            'quantity' => 1,
        ]);

        $stats = (new ReleaseUnpaidOrderStockJob)->handle();

        $this->assertSame(0, $stats['released']);
        $this->assertSame(1, $product->refresh()->stock);
        $this->assertSame('pending_payment', $fresh->refresh()->status);
        $this->assertSame('paid', $paid->refresh()->status);
    }

    public function test_artisan_command_runs_release_job(): void
    {
        config(['orders.reservation_ttl_minutes' => 30]);

        $product = $this->makeProduct(stock: 0);
        $order = $this->makePendingOrder(reservedAt: now()->subHour());
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'title' => 'Brick',
            'unit_price_cents' => 50,
            'quantity' => 2,
        ]);

        $this->artisan('orders:release-unpaid-stock')
            ->expectsOutputToContain('Released stock for 1 unpaid order(s).')
            ->assertSuccessful();

        $this->assertSame(2, $product->refresh()->stock);
        $this->assertSame('cancelled', $order->refresh()->status);
    }

    protected function makeProduct(int $stock): Product
    {
        $part = Part::factory()->create();

        return Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => Color::factory(),
            'stock' => $stock,
            'price' => 100,
        ]);
    }

    protected function makePendingOrder(\Illuminate\Support\Carbon $reservedAt): Order
    {
        $order = new Order;
        $order->forceFill([
            'number' => 'C2C-TEST-'.uniqid(),
            'status' => 'pending_payment',
            'email' => 'buyer@example.com',
            'name' => 'Buyer',
            'shipping_line1' => 'Street 1',
            'shipping_postal_code' => '1234AB',
            'shipping_city' => 'Amsterdam',
            'shipping_country_code' => 'NL',
            'subtotal_cents' => 100,
            'shipping_cents' => 395,
            'total_cents' => 495,
            'shipping_method_name' => 'PostNL',
            'payment_method' => 'ideal',
            'payment_provider' => 'manual',
            'stock_reserved_at' => $reservedAt,
        ])->save();

        return $order;
    }
}
