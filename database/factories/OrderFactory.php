<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(500, 10000);
        $shipping = 395;

        return [
            'user_id' => null,
            'number' => 'C2C-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'status' => 'pending_payment',
            'email' => fake()->safeEmail(),
            'name' => fake()->name(),
            'phone' => fake()->optional()->phoneNumber(),
            'shipping_line1' => fake()->streetAddress(),
            'shipping_postal_code' => fake()->postcode(),
            'shipping_city' => fake()->city(),
            'shipping_country_code' => 'NL',
            'subtotal_cents' => $subtotal,
            'shipping_cents' => $shipping,
            'total_cents' => $subtotal + $shipping,
            'shipping_method_name' => 'PostNL Brievenbus (NL)',
            'payment_method' => 'ideal',
            'payment_provider' => 'manual',
            'payment_reference' => (string) Str::uuid(),
            'stock_reserved_at' => now(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => 'paid',
            'paid_at' => now(),
            'stock_reserved_at' => null,
        ]);
    }
}
