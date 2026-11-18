<?php

declare(strict_types=1);

namespace App\Domain\Image\Jobs;

use App\Domain\Image\Jobs\Middleware\RespectCloudflareRateLimit;
use App\Integrations\Bricqer\Facades\Bricqer;
use App\Integrations\Cloudflare\Exceptions\ImageAlreadyExistsException;
use App\Integrations\Cloudflare\Exceptions\ImageSourceUnreachableException;
use App\Integrations\Cloudflare\Facades\Cloudflare;
use App\Models\Contracts\CloudflareImageable;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Hands a source URL to Cloudflare, which fetches and stores the image itself:
 * this server never downloads or converts it.
 */
class ImportImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const string QUEUE = 'images';

    /**
     * Rate limit releases must not count as failed attempts, so attempts are
     * bound by time (see retryUntil) and by the number of real exceptions.
     */
    public int $tries = 0;

    public int $maxExceptions = 3;

    public int $backoff = 60;

    /**
     * @param  class-string<Model&CloudflareImageable>  $modelClass
     */
    public function __construct(
        public string $modelClass,
        public int $modelId,
        public string $imageUrl,
    ) {
        $this->onQueue(self::QUEUE);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RespectCloudflareRateLimit];
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addDays(7);
    }

    public function handle(): void
    {
        $model = $this->modelClass::query()->find($this->modelId);

        if (! $model instanceof CloudflareImageable || $model->hasCloudflareImage()) {
            return;
        }

        $imageId = $model->cloudflareImageKey();
        $sourceUrl = $this->resolveSourceUrl();

        if ($sourceUrl === null) {
            Log::warning('Bricqer has no picture for image source.', [
                'image_id' => $imageId,
                'source_url' => $this->imageUrl,
            ]);

            $model->markCloudflareImageFailed();

            return;
        }

        try {
            $image = Cloudflare::images()->uploadFromUrl($imageId, $sourceUrl, ['source_url' => $sourceUrl]);

            $model->markCloudflareImageImported($image->id);
        } catch (ImageAlreadyExistsException) {
            $this->adoptExistingImage($model, $imageId, $sourceUrl);
        } catch (ImageSourceUnreachableException $exception) {
            Log::warning('Cloudflare could not fetch image source.', [
                'image_id' => $imageId,
                'source_url' => $sourceUrl,
                'error' => $exception->getMessage(),
            ]);

            $model->markCloudflareImageFailed();
        }
    }

    /**
     * Bricqer definitions hold a login protected relative path, which Cloudflare
     * can neither parse nor fetch. Resolved here rather than at dispatch, so a
     * full definition import doesn't make an extra request per image.
     */
    private function resolveSourceUrl(): ?string
    {
        if (! str_starts_with($this->imageUrl, '/')) {
            return $this->imageUrl;
        }

        return Bricqer::definition()->resolvePictureUrl($this->imageUrl);
    }

    /**
     * An existing image is only ours if it came from the same source: after a
     * database rebuild the same id could otherwise show another record's image.
     */
    private function adoptExistingImage(CloudflareImageable $model, string $imageId, string $sourceUrl): void
    {
        $existingImage = Cloudflare::images()->get($imageId);

        if (($existingImage->metadata['source_url'] ?? null) === $sourceUrl) {
            $model->markCloudflareImageImported($existingImage->id);

            return;
        }

        Log::error('Cloudflare image id already in use for a different source.', [
            'image_id' => $imageId,
            'source_url' => $sourceUrl,
            'existing_source_url' => $existingImage->metadata['source_url'] ?? null,
        ]);

        $model->markCloudflareImageFailed();
    }
}
