<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Pivots\InventoryMinifig;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Minifig extends Model
{
    /** @use HasFactory<\Database\Factories\MinifigFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'rebrickable_id',
        'bricklink_id',
        'name',
    ];

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
}
