<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Bulk-updates a single column for many rows in one query, using a SQL
 * `CASE key WHEN ... THEN ... END` expression instead of one UPDATE per row.
 */
final class BulkCaseUpdate
{
    /**
     * @param  array<int|string, string|int|float>  $valuesByKey  New $valueColumn value keyed by $keyColumn value.
     */
    public static function apply(string $table, string $keyColumn, string $valueColumn, array $valuesByKey): int
    {
        if ($valuesByKey === []) {
            return 0;
        }

        $cases = [];
        $bindings = [];

        foreach ($valuesByKey as $key => $value) {
            $cases[] = 'WHEN ? THEN ?';
            $bindings[] = $key;
            $bindings[] = $value;
        }

        $keys = array_keys($valuesByKey);
        $keyPlaceholders = implode(',', array_fill(0, count($keys), '?'));
        $caseSql = implode(' ', $cases);

        return DB::update(
            "UPDATE {$table}
             SET {$valueColumn} = CASE {$keyColumn} {$caseSql} END
             WHERE {$keyColumn} IN ({$keyPlaceholders})",
            [...$bindings, ...$keys],
        );
    }
}
