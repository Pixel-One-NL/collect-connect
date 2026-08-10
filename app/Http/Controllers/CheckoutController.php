<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Order\Actions\PlaceOrderAction;
use App\Domain\Shipping\ShippingMethodResolver;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private ShippingMethodResolver $shipping,
    ) {}

    public function show(): Response|RedirectResponse
    {
        $this->cartService->revalidateStock();
        $items = $this->cartService->getItems();

        if ($items->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $user = Auth::user();
        $address = $user?->addresses()->where('is_default', true)->first();
        $country = strtoupper((string) request()->query('country', $address?->country_code ?? 'NL'));

        try {
            $shippingMethods = $this->shipping->availableForCountry($country);
        } catch (ValidationException $exception) {
            return redirect()->route('cart.show')->withErrors($exception->errors());
        }

        return inertia('checkout/index', [
            'items' => $items,
            'subtotal' => $items->sum(fn (array $i): float => $i['price'] * $i['quantity']),
            'weight_grams' => $this->cartService->getTotalWeightGrams(),
            'shippingMethods' => $shippingMethods,
            'paymentMethods' => $this->paymentMethodsForCountry($country),
            'defaultAddress' => $address,
            'user' => $user ? ['name' => $user->name, 'email' => $user->email] : null,
            'country' => $country,
        ]);
    }

    public function store(StoreCheckoutRequest $request, PlaceOrderAction $placeOrder): RedirectResponse
    {
        $this->cartService->revalidateStock();
        $items = $this->cartService->getItems();

        if ($items->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $validated = $request->validated();
        $country = strtoupper($validated['country_code']);

        try {
            $order = $placeOrder->handle(
                data: $validated,
                cartItems: $items,
                allowedPaymentMethods: $this->paymentMethodsForCountry($country),
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        if (($validated['create_account'] ?? false) && Auth::check()) {
            $request->session()->regenerate();
            $this->cartService->mergeGuestCartIntoUser(Auth::id());
        }

        // Gateways that host their own payment page take over from here.
        $redirectUrl = data_get($order->meta, 'redirect_url');

        if (filled($redirectUrl)) {
            return redirect()->away($redirectUrl);
        }

        if (Auth::check()) {
            return redirect()->route('account.orders.show', $order)->with('status', 'Bestelling geplaatst.');
        }

        return redirect()->route('checkout.confirmation', $order)->with('status', 'Bestelling geplaatst.');
    }

    public function confirmation(Order $order): Response
    {
        $this->authorizeGuestConfirmation($order);

        $order->load('items');

        return inertia('checkout/confirmation', [
            'order' => OrderResource::make($order),
        ]);
    }

    protected function authorizeGuestConfirmation(Order $order): void
    {
        abort_unless(Gate::allows('viewPlaced', $order), 403, 'Deze bestelling is niet beschikbaar.');
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    protected function paymentMethodsForCountry(string $country): array
    {
        $common = [
            ['id' => 'card', 'label' => 'Creditcard'],
            ['id' => 'paypal', 'label' => 'PayPal'],
            ['id' => 'bank', 'label' => 'Bankoverschrijving'],
        ];

        return match ($country) {
            'NL' => [['id' => 'ideal', 'label' => 'iDEAL'], ...$common],
            'BE' => [['id' => 'bancontact', 'label' => 'Bancontact'], ...$common],
            default => $common,
        };
    }
}
