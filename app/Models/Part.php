<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Pivots\InventoryPart;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Part extends Model
{
    /** @use HasFactory<\Database\Factories\PartFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'part_category_id',
        'rebrickable_id',
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
     * @return MorphOne<Product, $this>
     */
    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }
}
