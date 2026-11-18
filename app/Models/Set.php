<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCloudflareImage;
use App\Models\Contracts\CloudflareImageable;
use App\Models\Pivots\InventorySet;
use Database\Factories\SetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Set extends Model implements CloudflareImageable
{
    /** @use HasFactory<SetFactory> */
    use HasCloudflareImage, HasFactory, Searchable;

    protected $fillable = [
        'set_num',
        'name',
        'year',
        'theme_id',
        'num_parts',
        'img_url',
    ];

    protected function cloudflareImageType(): string
    {
        return 'set';
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Theme, $this> */
    public function theme(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_id', 'rebrickable_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<Inventory, $this> */
    public function latestInventory(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Inventory::class)->ofMany(['version' => 'max', 'id' => 'max']);
    }

    /** @return HasMany<Inventory, $this> */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Inventories that contain this set as a sub-set.
     *
     * @return BelongsToMany<Inventory, $this, InventorySet>
     */
    public function parentInventories(): BelongsToMany
    {
        return $this
            ->belongsToMany(Inventory::class, 'inventory_sets')
            ->using(InventorySet::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'set_num' => (string) $this->set_num,
            'name' => (string) $this->name,
        ];
    }
}
