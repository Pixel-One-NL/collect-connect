<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\Color;
use App\Models\Concerns\HasCloudflareImage;
use App\Models\Contracts\CloudflareImageable;
use App\Models\Part;
use Database\Factories\PartColorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PartColor extends Pivot implements CloudflareImageable
{
    /** @use HasFactory<PartColorFactory> */
    use HasCloudflareImage, HasFactory;

    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'part_colors';

    protected $fillable = [
        'part_id',
        'color_id',
        'bricqer_definition_id',
    ];

    protected static function newFactory(): PartColorFactory
    {
        return PartColorFactory::new();
    }

    /**
     * @return BelongsTo<Part, $this>
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * @return BelongsTo<Color, $this>
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    protected function cloudflareImageType(): string
    {
        return 'part-color';
    }
}
