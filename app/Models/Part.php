<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Pivots\InventoryPart;
use App\Models\Pivots\PartColor;
use Database\Factories\PartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Scout\Searchable;

class Part extends Model
{
    /** @use HasFactory<PartFactory> */
    use HasFactory, Searchable;

    public $timestamps = false;

    protected $fillable = [
        'part_category_id',
        'rebrickable_id',
        'bricklink_id',
        'name',
    ];

    /**
     * @return BelongsTo<PartCategory, $this>
     */
    public function partCategory(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class);
    }

    /**
     * @return BelongsToMany<Inventory, $this, InventoryPart>
     */
    public function inventories(): BelongsToMany
    {
        return $this
            ->belongsToMany(Inventory::class, 'inventory_parts')
            ->using(InventoryPart::class);
    }

    /**
     * Per-color image rows. Each holds the Rebrickable image for one color of
     * this part via Spatie MediaLibrary.
     *
     * @return HasMany<PartColor, $this>
     */
    public function partColors(): HasMany
    {
        return $this->hasMany(PartColor::class);
    }

    /**
     * @return BelongsToMany<Color, $this, PartColor>
     */
    public function colors(): BelongsToMany
    {
        return $this
            ->belongsToMany(Color::class, 'part_colors')
            ->using(PartColor::class);
    }

    /**
     * @return MorphOne<Product, $this>
     */
    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }

    /**
     * A part has one product per color.
     *
     * @return MorphMany<Product, $this>
     */
    public function products(): MorphMany
    {
        return $this->morphMany(Product::class, 'productable');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'rebrickable_id' => (string) $this->rebrickable_id,
            'bricklink_id' => (string) $this->bricklink_id,
            'ldraw_id' => (string) $this->ldraw_id,
        ];
    }
}
