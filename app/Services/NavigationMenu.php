<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PartCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Builds the storefront navigation tree from `config('navigation.menu')`,
 * expanding items with a `source` into children generated from the catalog.
 *
 * @phpstan-type MenuItem array{id: string, label: string, href?: ?string, children?: array<int, array<string, mixed>>}
 */
class NavigationMenu
{
    public const CACHE_KEY = 'navigation.menu';

    /**
     * @return array<int, MenuItem>
     */
    public function items(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), fn (): array => array_map(
            fn (array $item): array => $this->expand($item),
            config('navigation.menu', []),
        ));
    }

    /**
     * @param  array{id: string, label: string, href?: ?string, source?: string}  $item
     * @return MenuItem
     */
    protected function expand(array $item): array
    {
        $source = $item['source'] ?? null;
        unset($item['source']);

        if ($source === 'part_categories') {
            $item['children'] = $this->partCategories();
        }

        return $item;
    }

    /**
     * Part categories that currently have purchasable products, linking to the
     * parts catalog filtered on that category.
     *
     * @return array<int, array{id: string, label: string, href: string}>
     */
    protected function partCategories(): array
    {
        return PartCategory::query()
            ->whereHas('parts.products', fn (Builder $product) => $product->purchasable())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (PartCategory $category): array => [
                'id' => "parts-category-{$category->id}",
                'label' => $category->name,
                'href' => route('catalog.parts', ['category_id' => $category->id], absolute: false),
            ])
            ->all();
    }
}
