<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCloudflareImage;
use App\Models\Contracts\CloudflareImageable;
use App\Models\Pivots\InventoryMinifig;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Scout\Searchable;

class Minifig extends Model implements CloudflareImageable
{
    /** @use HasFactory<\Database\Factories\MinifigFactory> */
    use HasCloudflareImage, HasFactory, Searchable;

    public $timestamps = false;

    protected $fillable = [
        'rebrickable_id',
        'bricklink_id',
        'name',
        'weight_grams',
        'bricqer_definition_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_grams' => 'float',
        ];
    }

    protected function cloudflareImageType(): string
    {
        return 'minifig';
    }

    /**
     * @return BelongsToMany<Inventory, $this, InventoryMinifig>
     */
    public function inventories(): BelongsToMany
    {
        return $this
            ->belongsToMany(Inventory::class, 'inventory_minifigs')
            ->using(InventoryMinifig::class);
    }

    /**
     * @return MorphOne<Product, $this>
     */
    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }

    /**
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
            'bricklink_id' => (string) ($this->bricklink_id ?? ''),
            'name' => (string) $this->name,
        ];
    }
}
