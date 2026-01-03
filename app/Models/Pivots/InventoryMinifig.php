<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\Inventory;
use App\Models\Part;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InventoryMinifig extends Pivot
{
    protected $fillable = [
        'rebrickable_id',
        'inventory_id',
        'part_id',
    ];

    /**
     * @return BelongsTo<Inventory, $this>
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * @return BelongsTo<Part, $this>
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
