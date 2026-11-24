<?php

declare(strict_types=1);

namespace App\Domain\Minifig\Queries;

use App\Models\Inventory;
use App\Models\Minifig;
use App\Models\Product;
use App\Models\Set;
use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Theme lookups for the minifig catalog. Minifigs reach a theme through
 * inventories → set → theme, so these queries walk that path.
 */
final class MinifigThemeQuery
{
    public const CACHE_KEY = 'catalog.minifig-themes';

    private const MAX_DEPTH = 3;

    /**
     * Themes that contain purchasable minifigs, plus their ancestor themes.
     *
     * @return list<array{id: int, name: string, parent_id: ?int}>
     */
    public static function available(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), function (): array {
            $minifigIds = Product::query()
                ->select('productable_id')
                ->where('productable_type', (new Minifig)->getMorphClass())
                ->purchasable();

            $setIds = Inventory::query()
                ->select('set_id')
                ->whereIn('id', DB::table('inventory_minifigs')->select('inventory_id')->whereIn('minifig_id', $minifigIds));

            $themeIds = Set::query()
                ->whereIn('id', $setIds)
                ->whereNotNull('theme_id')
                ->distinct()
                ->pluck('theme_id')
                ->all();

            $themes = collect();
            $pending = $themeIds;

            for ($depth = 0; $pending !== [] && $depth <= self::MAX_DEPTH; $depth++) {
                $found = Theme::query()
                    ->whereIn('rebrickable_id', $pending)
                    ->get(['rebrickable_id', 'name', 'parent_id'])
                    ->keyBy('rebrickable_id');

                $themes = $themes->union($found);
                $pending = $found->pluck('parent_id')->filter()->reject(fn (mixed $id): bool => $themes->has($id))->unique()->values()->all();
            }

            return $themes
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->map(fn (Theme $theme): array => [
                    'id' => (int) $theme->rebrickable_id,
                    'name' => $theme->name,
                    'parent_id' => $theme->parent_id !== null ? (int) $theme->parent_id : null,
                ])
                ->values()
                ->all();
        });
    }

    /**
     * The given theme's rebrickable id plus those of its children, up to three levels deep.
     *
     * @return list<int>
     */
    public static function withDescendants(int $themeId): array
    {
        $ids = [$themeId];
        $parents = [$themeId];

        for ($depth = 0; $parents !== [] && $depth < self::MAX_DEPTH; $depth++) {
            $parents = Theme::query()
                ->whereIn('parent_id', $parents)
                ->whereNotIn('rebrickable_id', $ids)
                ->pluck('rebrickable_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            $ids = [...$ids, ...$parents];
        }

        return $ids;
    }
}
