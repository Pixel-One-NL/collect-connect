<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * Customer-facing fields only. Lifecycle fields (status, payment, tracking,
     * stock reservation) are set via forceFill inside domain actions.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'number',
        'email',
        'name',
        'phone',
        'shipping_line1',
        'shipping_line2',
        'shipping_postal_code',
        'shipping_city',
        'shipping_country_code',
        'subtotal_cents',
        'shipping_cents',
        'total_cents',
        'shipping_method_name',
        'shipping_method_id',
        'payment_method',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'stock_reserved_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
