<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use Binafy\LaravelCart\Drivers\Driver;
use Binafy\LaravelCart\LaravelCart;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Must stay in sync with LaravelCartSession::SESSION_KEY_PREFIX.
     */
    private const SESSION_KEY_PREFIX = 'cart_';

    /**
     * Returns the authenticated user ID or a session-scoped guest UUID.
     * Always returns a non-null string to avoid the session driver's null-userId TypeError.
     */
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

        /**
         * Write directly to avoid LaravelCartSession::storeItem() serialising the full
         * Eloquent model into the session. The format is what the package expects for
         * increaseQuantity / decreaseQuantity / removeItem to work correctly.
         */
        $raw = $this->getRawItems();
        $raw[] = [
            'itemable_id' => $product->getKey(),
            'itemable_type' => Product::class,
            'quantity' => min($quantity, $product->stock),
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

    /**
     * @return Collection<int, array{id: int, name: string, image: string|null, price: float, quantity: int, stock: int, color: string|null, color_hex: string|null}>
     */
    public function getItems(): Collection
    {
        $rawItems = $this->getRawItems();

        if (empty($rawItems)) {
            return collect();
        }

        $productIds = array_column($rawItems, 'itemable_id');
        $products = Product::whereIn('id', $productIds)
            ->with(['productable.partColors.media', 'color'])
            ->get()
            ->keyBy('id');

        return collect($rawItems)
            ->map(function (array $item) use ($products): ?array {
                /** @var Product|null $product */
                $product = $products[$item['itemable_id']] ?? null;

                if ($product === null) {
                    return null;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->productable->name,
                    'image' => $product->productable instanceof Part
                        ? $product->productable->partColors->firstWhere('color_id', $product->color_id)
                            ?->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION)
                            ?->getAvailableUrl([PartColor::THUMB_CONVERSION])
                        : null,
                    'price' => $product->price / 100,
                    'quantity' => $item['quantity'],
                    'stock' => $product->stock,
                    'color' => $product->color?->name,
                    'color_hex' => $product->color?->hex,
                ];
            })
            ->filter()
            ->values();
    }

    public function getTotal(): int
    {
        $rawItems = $this->getRawItems();

        if (empty($rawItems)) {
            return 0;
        }

        $productIds = array_column($rawItems, 'itemable_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return (int) collect($rawItems)->sum(function (array $item) use ($products): int {
            $product = $products[$item['itemable_id']] ?? null;

            return $product !== null ? (int) $product->price * $item['quantity'] : 0;
        });
    }
}
