<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Pivots\InventoryMinifig;
use App\Models\Pivots\InventoryPart;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'rebrickable_id',
    ];

    /**
     * @return BelongsToMany<Part, $this, InventoryPart>
     */
    public function parts(): BelongsToMany
    {
        return $this
            ->belongsToMany(Part::class)
            ->using(InventoryPart::class);
    }

    /**
     * @return BelongsToMany<Minifig, $this, InventoryMinifig>
     */
    public function minifigs(): BelongsToMany
    {
        return $this
            ->belongsToMany(Minifig::class)
            ->using(InventoryMinifig::class);
    }
}
