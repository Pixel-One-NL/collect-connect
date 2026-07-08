<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartCategory extends Model
{
    /** @use HasFactory<\Database\Factories\PartCategoryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'rebrickable_id',
        'name',
    ];

    /**
     * @return HasMany<Part, $this>
     */
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
