<?php

declare(strict_types=1);

namespace App\Domain\Shipping;

use App\Models\ShippingMethod;
use Illuminate\Validation\ValidationException;

class ShippingMethodResolver
{
    /**
     * @return list<array{id: int, name: string, price_cents: int, code: string}>
     */
    public function availableForCountry(string $country): array
    {
        $country = strtoupper($country);

        $methods = ShippingMethod::query()
            ->where('is_active', true)
            ->orderBy('price_cents')
            ->get()
            ->filter(function (ShippingMethod $method) use ($country): bool {
                $countries = $method->countries ?? [];

                return $countries === [] || in_array($country, $countries, true);
            })
            ->map(fn (ShippingMethod $method): array => [
                'id' => $method->id,
                'name' => $method->name,
                'price_cents' => $method->price_cents,
                'code' => (string) ($method->code ?? ''),
            ])
            ->values()
            ->all();

        if ($methods !== []) {
            return $methods;
        }

        if (! config('shipping.allow_demo_fallback')) {
            throw ValidationException::withMessages([
                'shipping_method_id' => 'Er zijn momenteel geen verzendmethodes beschikbaar.',
            ]);
        }

        return [
            [
                'id' => 0,
                'name' => 'PostNL Brievenbus (NL)',
                'price_cents' => 395,
                'code' => 'NL',
            ],
            [
                'id' => -1,
                'name' => 'PostNL Pakket (EU)',
                'price_cents' => 995,
                'code' => 'EUR2',
            ],
        ];
    }

    /**
     * @return array{id: int, name: string, price_cents: int}
     */
    public function resolve(int $shippingMethodId, string $country): array
    {
        foreach ($this->availableForCountry($country) as $method) {
            if ((int) $method['id'] === $shippingMethodId) {
                return [
                    'id' => (int) $method['id'],
                    'name' => (string) $method['name'],
                    'price_cents' => (int) $method['price_cents'],
                ];
            }
        }

        throw ValidationException::withMessages([
            'shipping_method_id' => 'Kies een geldige verzendmethode voor dit land.',
        ]);
    }
}
