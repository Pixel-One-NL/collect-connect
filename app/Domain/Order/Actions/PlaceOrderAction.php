<?php

declare(strict_types=1);

namespace App\Domain\Order\Actions;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Shipping\ShippingMethodResolver;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlaceOrderAction
{
    public function __construct(
        private CartService $cartService,
        private ShippingMethodResolver $shipping,
        private PaymentGateway $payments,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     phone?: ?string,
     *     company?: ?string,
     *     line1: string,
     *     line2?: ?string,
     *     postal_code: string,
     *     city: string,
     *     country_code: string,
     *     shipping_method_id: int,
     *     payment_method: string,
     *     create_account?: bool,
     *     password?: ?string
     * }  $data
     * @param  Collection<int, array{id: int, name: string, quantity: int, price: float, lego_number?: ?string, color?: ?string}>  $cartItems
     * @param  list<array{id: string, label: string}>  $allowedPaymentMethods
     */
    public function handle(array $data, Collection $cartItems, array $allowedPaymentMethods): Order
    {
        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Je winkelwagen is leeg.',
            ]);
        }

        $country = strtoupper($data['country_code']);

        if (! collect($allowedPaymentMethods)->contains(fn (array $m): bool => $m['id'] === $data['payment_method'])) {
            throw ValidationException::withMessages([
                'payment_method' => 'Betaalmethode niet beschikbaar voor dit land.',
            ]);
        }

        $shipping = $this->shipping->resolve((int) $data['shipping_method_id'], $country);

        $order = DB::transaction(function () use ($data, $cartItems, $shipping, $country): Order {
            $user = Auth::user();

            if (! $user && ($data['create_account'] ?? false) && filled($data['password'] ?? null)) {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make((string) $data['password']),
                ]);

                Auth::login($user);
                // Session regeneration (fixation defense) happens in the controller
                // after this transaction so cart merge still sees the guest session key.
                $this->cartService->mergeGuestCartIntoUser($user->id);
            }

            if ($user) {
                $user->addresses()->update(['is_default' => false]);
                $user->addresses()->create([
                    'name' => $data['name'],
                    'company' => $data['company'] ?? null,
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'line1' => $data['line1'],
                    'line2' => $data['line2'] ?? null,
                    'postal_code' => $data['postal_code'],
                    'city' => $data['city'],
                    'country_code' => $country,
                    'is_default' => true,
                ]);
            }

            $order = new Order;
            $order->forceFill([
                'user_id' => $user?->id,
                'number' => 'C2C-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'status' => 'pending_payment',
                'email' => $data['email'],
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'shipping_company' => $data['company'] ?? null,
                'shipping_line1' => $data['line1'],
                'shipping_line2' => $data['line2'] ?? null,
                'shipping_postal_code' => $data['postal_code'],
                'shipping_city' => $data['city'],
                'shipping_country_code' => $country,
                'subtotal_cents' => 0,
                'shipping_cents' => $shipping['price_cents'],
                'total_cents' => 0,
                'shipping_method_name' => $shipping['name'],
                'shipping_method_id' => $shipping['id'] > 0 ? $shipping['id'] : null,
                'payment_method' => $data['payment_method'],
                'payment_provider' => 'manual',
            ])->save();

            $subtotalCents = 0;

            foreach ($cartItems as $item) {
                $product = Product::query()
                    ->with(['productable', 'color'])
                    ->lockForUpdate()
                    ->find($item['id']);

                if (! $product || $product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => 'Voorraad onvoldoende voor '.($item['name'] ?? 'een product').'.',
                    ]);
                }

                $unitPriceCents = (int) $product->price;
                $subtotalCents += $unitPriceCents * (int) $item['quantity'];

                $product->decrement('stock', (int) $item['quantity']);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'title' => (string) ($product->productable?->name ?? $item['name']),
                    'lego_number' => $item['lego_number'] ?? data_get($product->productable, 'bricklink_id'),
                    'color_name' => $item['color'] ?? $product->color?->name,
                    'unit_price_cents' => $unitPriceCents,
                    'quantity' => (int) $item['quantity'],
                ]);
            }

            $payment = $this->payments->initiate($order);

            $order->forceFill([
                'subtotal_cents' => $subtotalCents,
                'total_cents' => $subtotalCents + $shipping['price_cents'],
                'status' => 'pending_payment',
                'stock_reserved_at' => now(),
                'payment_provider' => $payment->provider,
                'payment_reference' => $payment->reference,
                'meta' => [
                    'note' => $payment->provider === 'bank_transfer'
                        ? 'Awaiting bank transfer. Mark paid after funds clear.'
                        : 'Awaiting payment provider confirmation.',
                    // Consumed by CheckoutController::store() to hand the customer
                    // off to the gateway's own payment page when it has one.
                    'redirect_url' => $payment->redirectUrl,
                    'noshop_payload_ready' => true,
                ],
            ])->save();

            $this->cartService->clear();

            return $order->refresh();
        });

        $placed = session('checkout.placed_order_ids', []);
        $placed[] = $order->id;
        session(['checkout.placed_order_ids' => array_values(array_unique($placed))]);

        return $order;
    }
}
