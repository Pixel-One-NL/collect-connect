<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'stock',
        'price',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function productable(): MorphTo
    {
        return $this->morphTo('productable');
    }
}
