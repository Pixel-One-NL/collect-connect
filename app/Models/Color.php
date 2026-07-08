<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Pivots\PartColor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    /** @use HasFactory<\Database\Factories\ColorFactory> */
    use HasFactory;

    protected $fillable = [
        'rebrickable_id',
        'bricqer_color_id',
        'bricklink_color_id',
        'name',
        'hex',
        'is_transparent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bricqer_color_id' => 'integer',
            'is_transparent' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<PartColor, $this>
     */
    public function partColors(): HasMany
    {
        return $this->hasMany(PartColor::class);
    }
}
