<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Pivots\InventoryMinifig;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Minifig extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\MinifigFactory> */
    use HasFactory, InteractsWithMedia, Searchable;

    public const string BRICQER_IMAGE_COLLECTION = 'bricqer';

    public const string THUMB_CONVERSION = 'thumb';

    public const string MEDIUM_CONVERSION = 'medium';

    public const string LARGE_CONVERSION = 'large';

    public $timestamps = false;

    protected $fillable = [
        'rebrickable_id',
        'bricklink_id',
        'name',
        'weight_grams',
        'bricqer_definition_id',
        'bricqer_image_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_grams' => 'float',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::BRICQER_IMAGE_COLLECTION)
            ->singleFile();
    }

    /**
     * Bricqer minifig images are stored once on the model (minifigs have no
     * color variants) and served in three sizes, matching part-color images.
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

    /**
     * @return BelongsToMany<Inventory, $this, InventoryMinifig>
     */
    public function inventories(): BelongsToMany
    {
        return $this
            ->belongsToMany(Inventory::class, 'inventory_minifigs')
            ->using(InventoryMinifig::class);
    }

    /**
     * @return MorphOne<Product, $this>
     */
    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }

    /**
     * @return MorphMany<Product, $this>
     */
    public function products(): MorphMany
    {
        return $this->morphMany(Product::class, 'productable');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'rebrickable_id' => (string) $this->rebrickable_id,
            'bricklink_id' => (string) ($this->bricklink_id ?? ''),
            'name' => (string) $this->name,
        ];
    }
}
