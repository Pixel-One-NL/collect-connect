<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Minifig;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use App\Support\MediaUrl;
use Binafy\LaravelCart\Drivers\Driver;
use Binafy\LaravelCart\LaravelCart;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Must stay in sync with LaravelCartSession::SESSION_KEY_PREFIX.
     */
    private const SESSION_KEY_PREFIX = 'cart_';

    /**
     * @return list<string>
     */
    public function revalidateStock(): array
    {
        $messages = [];
        $raw = $this->getRawItems();
        $changed = false;

        if ($raw === []) {
            return $messages;
        }

        $products = Product::query()
            ->whereIn('id', array_column($raw, 'itemable_id'))
            ->get()
            ->keyBy('id');

        $next = [];

        foreach ($raw as $item) {
            $product = $products->get($item['itemable_id'] ?? 0);

            if ($product === null || $product->stock <= 0) {
                $messages[] = 'Een artikel is niet meer beschikbaar en is uit je winkelwagen gehaald.';
                $changed = true;

                continue;
            }

            if ($item['quantity'] > $product->stock) {
                $item['quantity'] = $product->stock;
                $messages[] = "Voorraad bijgewerkt voor product #{$product->id}.";
                $changed = true;
            }

            $next[] = $item;
        }

        if ($changed) {
            session()->put($this->sessionKey(), $next);
        }

        return array_values(array_unique($messages));
    }

    protected function userId(): string
    {
        if (auth()->check()) {
            return (string) auth()->id();
        }

        if (! session()->has('cart_guest_id')) {
            session()->put('cart_guest_id', (string) Str::uuid());
        }

        return (string) session('cart_guest_id');
    }

    protected function driver(): Driver
    {
        return LaravelCart::driver('session');
    }

    protected function sessionKey(): string
    {
        return self::SESSION_KEY_PREFIX.$this->userId();
    }

    /**
     * @return array<int, array{itemable_id: int, itemable_type: string, quantity: int}>
     */
    public function getRawItems(): array
    {
        return session($this->sessionKey(), []);
    }

    /**
     * @return array{itemable_id: int, itemable_type: string, quantity: int}|null
     */
    protected function findRawItem(Product $product): ?array
    {
        foreach ($this->getRawItems() as $item) {
            if (isset($item['itemable_id']) && $item['itemable_id'] === $product->getKey()) {
                return $item;
            }
        }

        return null;
    }

    public function addItem(Product $product, int $quantity): void
    {
        $userId = $this->userId();
        $existing = $this->findRawItem($product);

        if ($existing !== null) {
            $delta = min($existing['quantity'] + $quantity, $product->stock) - $existing['quantity'];

            if ($delta > 0) {
                $this->driver()->increaseQuantity($product, $delta, $userId);
            }

            return;
        }

        $raw = $this->getRawItems();
        $raw[] = [
            'itemable_id' => $product->getKey(),
            'itemable_type' => Product::class,
            'quantity' => min($quantity, max(0, $product->stock)),
        ];
        session()->put($this->sessionKey(), $raw);
    }

    public function removeItem(Product $product): void
    {
        $this->driver()->removeItem($product, $this->userId());
    }

    public function setQuantity(Product $product, int $quantity): void
    {
        $existing = $this->findRawItem($product);

        if ($existing === null) {
            return;
        }

        $cappedQty = min($quantity, $product->stock);
        $delta = $cappedQty - $existing['quantity'];
        $userId = $this->userId();

        if ($delta > 0) {
            $this->driver()->increaseQuantity($product, $delta, $userId);
        } elseif ($delta < 0) {
            $this->driver()->decreaseQuantity($product, abs($delta), $userId);
        }
    }

    public function clear(): void
    {
        $this->driver()->emptyCart($this->userId());
    }

    public function mergeGuestCartIntoUser(int|string $userId): void
    {
        $guestKey = self::SESSION_KEY_PREFIX.(string) session('cart_guest_id');
        $userKey = self::SESSION_KEY_PREFIX.(string) $userId;
        $guestItems = session($guestKey, []);

        if ($guestItems === []) {
            return;
        }

        $userItems = session($userKey, []);
        $byId = collect($userItems)->keyBy('itemable_id');

        foreach ($guestItems as $item) {
            $id = $item['itemable_id'] ?? null;
            if ($id === null) {
                continue;
            }
            if ($byId->has($id)) {
                $byId[$id]['quantity'] = ($byId[$id]['quantity'] ?? 0) + ($item['quantity'] ?? 0);
            } else {
                $byId[$id] = $item;
            }
        }

        $productStocks = Product::query()
            ->whereIn('id', $byId->keys()->all())
            ->pluck('stock', 'id');

        $merged = $byId->map(function (array $item) use ($productStocks): array {
            $id = $item['itemable_id'] ?? null;
            $stock = (int) ($productStocks[$id] ?? 0);
            $item['quantity'] = max(0, min((int) ($item['quantity'] ?? 0), $stock));

            return $item;
        })->filter(fn (array $item): bool => ($item['quantity'] ?? 0) > 0)->values()->all();

        session()->put($userKey, $merged);
        session()->forget($guestKey);
    }

    /**
     * @return Collection<int, array{id: int, name: string, image: string|null, price: float, quantity: int, stock: int, color: string|null, color_hex: string|null, lego_number: string|null, weight_grams: float|null}>
     */
    public function getItems(): Collection
    {
        $this->revalidateStock();
        $rawItems = $this->getRawItems();

        if ($rawItems === []) {
            return collect();
        }

        $productIds = array_column($rawItems, 'itemable_id');
        $products = Product::whereIn('id', $productIds)
            ->with([
                'color',
                'productable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Part::class => ['partColors.media'],
                    Minifig::class => ['media'],
                ]),
            ])
            ->get()
            ->keyBy('id');

        return collect($rawItems)
            ->map(function (array $item) use ($products): ?array {
                /** @var Product|null $product */
                $product = $products[$item['itemable_id']] ?? null;

                if ($product === null) {
                    return null;
                }

                $unitWeight = $this->unitWeightGrams($product);

                return [
                    'id' => $product->id,
                    'name' => $product->productable->name,
                    'lego_number' => $product->productable->bricklink_id ?? null,
                    'image' => $this->imageFor($product),
                    'price' => $product->price / 100,
                    'quantity' => $item['quantity'],
                    'stock' => $product->stock,
                    'color' => $product->color?->name,
                    'color_hex' => $product->color?->hex,
                    'weight_grams' => $unitWeight,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Total cart weight in grams (sum of unit weight × quantity).
     * Lines without a known weight are excluded from the sum.
     */
    public function getTotalWeightGrams(): float
    {
        return (float) $this->getItems()->sum(
            fn (array $item): float => (($item['weight_grams'] ?? 0) * $item['quantity']),
        );
    }

    protected function unitWeightGrams(Product $product): ?float
    {
        $weight = data_get($product->productable, 'weight_grams');

        if ($weight === null) {
            return null;
        }

        return (float) $weight;
    }

    public function getTotal(): int
    {
        $rawItems = $this->getRawItems();

        if ($rawItems === []) {
            return 0;
        }

        $productIds = array_column($rawItems, 'itemable_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return (int) collect($rawItems)->sum(function (array $item) use ($products): int {
            $product = $products[$item['itemable_id']] ?? null;

            return $product !== null ? (int) $product->price * $item['quantity'] : 0;
        });
    }

    /**
     * Cart thumbnails are media-library only (no Bricqer CDN hotlinking).
     */
    protected function imageFor(Product $product): ?string
    {
        if ($product->productable instanceof Part) {
            $partColor = $product->productable->partColors->firstWhere('color_id', $product->color_id);

            return MediaUrl::fromMedia(
                $partColor?->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION),
                [PartColor::THUMB_CONVERSION, PartColor::MEDIUM_CONVERSION, PartColor::LARGE_CONVERSION],
            );
        }

        if ($product->productable instanceof Minifig) {
            return MediaUrl::fromMedia(
                $product->productable->getFirstMedia(Minifig::BRICQER_IMAGE_COLLECTION),
                [Minifig::THUMB_CONVERSION, Minifig::MEDIUM_CONVERSION, Minifig::LARGE_CONVERSION],
            );
        }

        return null;
    }
}
