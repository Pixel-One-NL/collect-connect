<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\Color;
use App\Models\Part;
use Database\Factories\PartColorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PartColor extends Pivot implements HasMedia
{
    /** @use HasFactory<PartColorFactory> */
    use HasFactory, InteractsWithMedia;

    public const string IMAGE_COLLECTION = 'image';

    public const string LDRAW_IMAGE_COLLECTION = 'ldraw';

    public const string BRICQER_IMAGE_COLLECTION = 'bricqer';

    public const string THUMB_CONVERSION = 'thumb';

    public const string MEDIUM_CONVERSION = 'medium';

    public const string LARGE_CONVERSION = 'large';

    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'part_colors';

    protected $fillable = [
        'part_id',
        'color_id',
        'bricqer_definition_id',
        'bricqer_image_url',
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGE_COLLECTION)
            ->singleFile();

        $this->addMediaCollection(self::LDRAW_IMAGE_COLLECTION)
            ->singleFile();

        $this->addMediaCollection(self::BRICQER_IMAGE_COLLECTION)
            ->singleFile();
    }

    /**
     * Bricqer catalog images are imported once and served in three sizes:
     * a small webp thumb for realtime search results, a medium size for
     * product cards and a large one for the single product page. Conversions
     * are queued (the media library default) so the import jobs stay fast.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(self::THUMB_CONVERSION)
            ->performOnCollections(self::BRICQER_IMAGE_COLLECTION)
            ->fit(Fit::Contain, 200, 200)
            ->format('webp');

        $this->addMediaConversion(self::MEDIUM_CONVERSION)
            ->performOnCollections(self::BRICQER_IMAGE_COLLECTION)
            ->fit(Fit::Contain, 400, 400)
            ->format('webp');

        $this->addMediaConversion(self::LARGE_CONVERSION)
            ->performOnCollections(self::BRICQER_IMAGE_COLLECTION)
            ->fit(Fit::Contain, 800, 800)
            ->format('webp');
    }
}
