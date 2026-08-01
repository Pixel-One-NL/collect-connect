<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = [
        'bricqer_id',
        'name',
        'code',
        'area',
        'price_cents',
        'track_trace',
        'countries',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'track_trace' => 'boolean',
            'is_active' => 'boolean',
            'countries' => 'array',
        ];
    }
}
