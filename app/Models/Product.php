<?php

declare(strict_types=1);

namespace App\Models;

use Binafy\LaravelCart\Cartable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;

class Product extends Model implements Cartable
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'productable_type',
        'productable_id',
        'stock',
        'price',
        'color_id',
    ];

    /**
     * @return MorphTo<Part|Minifig, $this>
     */
    public function productable(): MorphTo
    {
        return $this->morphTo('productable');
    }

    /**
     * @return BelongsTo<Color, $this>
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function getPrice(): float
    {
        return $this->price / 100;
    }

    /**
     * The searchable data for the product. The name and BrickLink number are
     * pulled from the polymorphic productable (a part or a minifig).
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing('productable');

        return [
            'id' => (string) $this->id,
            'name' => (string) ($this->productable->name ?? ''),
            'bricklink_id' => (string) data_get($this->productable, 'bricklink_id', ''),
            'price' => (int) $this->price,
            'stock' => (int) $this->stock,
        ];
    }

    /**
     * Eager load the productable when (re)building the whole search index to
     * avoid an N+1 query per product.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('productable');
    }
}
