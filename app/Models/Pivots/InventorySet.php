<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\Inventory;
use App\Models\Set;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InventorySet extends Pivot
{
    public $timestamps = false;

    protected $table = 'inventory_sets';

    protected $fillable = [
        'inventory_id',
        'set_id',
        'quantity',
    ];

    /**
     * @return BelongsTo<Inventory, $this>
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * @return BelongsTo<Set, $this>
     */
    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }
}
