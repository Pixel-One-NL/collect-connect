<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TrendingProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A product hand-picked in the admin panel for the "Trending" row on the
 * homepage. Ordering is driven by `sort_order` so editors can reorder the row.
 */
class TrendingProduct extends Model
{
    /** @use HasFactory<TrendingProductFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sort_order',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
