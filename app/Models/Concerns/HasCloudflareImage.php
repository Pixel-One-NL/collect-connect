<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Domain\Image\Jobs\DeleteCloudflareImageJob;
use App\Support\ImageUrl;
use App\Support\ImageVariant;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string|null $cloudflare_image_id
 * @property \Illuminate\Support\Carbon|null $cloudflare_image_failed_at
 */
trait HasCloudflareImage
{
    /**
     * Name of this record type within the custom image id.
     */
    abstract protected function cloudflareImageType(): string;

    protected static function bootHasCloudflareImage(): void
    {
        static::deleted(function (self $model): void {
            if ($model->cloudflare_image_id !== null) {
                DeleteCloudflareImageJob::dispatch($model->cloudflare_image_id);
            }
        });
    }

    protected function initializeHasCloudflareImage(): void
    {
        $this->mergeCasts(['cloudflare_image_failed_at' => 'datetime']);
    }

    /**
     * Deterministic id, so a retried upload can never create a duplicate image.
     */
    public function cloudflareImageKey(): string
    {
        return sprintf('%s-%s-%s', config('cloudflare.id_prefix'), $this->cloudflareImageType(), $this->getKey());
    }

    public function hasCloudflareImage(): bool
    {
        return $this->cloudflare_image_id !== null;
    }

    /**
     * Writes through the query builder: saving the model would trigger a
     * synchronous Typesense update for every imported image.
     */
    public function markCloudflareImageImported(string $imageId): void
    {
        $this->newQuery()->whereKey($this->getKey())->update([
            'cloudflare_image_id' => $imageId,
            'cloudflare_image_failed_at' => null,
        ]);
    }

    public function markCloudflareImageFailed(): void
    {
        $this->newQuery()->whereKey($this->getKey())->update([
            'cloudflare_image_failed_at' => now(),
        ]);
    }

    public function imageUrl(ImageVariant $variant = ImageVariant::Large): ?string
    {
        return ImageUrl::fromId($this->cloudflare_image_id, $variant);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutCloudflareImage(Builder $query): Builder
    {
        return $query->whereNull($query->qualifyColumn('cloudflare_image_id'));
    }

    /**
     * Records still to upload, skipping known dead source URLs so routine imports
     * don't spend API budget on them.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeImportable(Builder $query): Builder
    {
        return $query
            ->whereNull($query->qualifyColumn('cloudflare_image_id'))
            ->whereNull($query->qualifyColumn('cloudflare_image_failed_at'));
    }
}
