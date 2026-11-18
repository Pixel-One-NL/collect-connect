<?php

declare(strict_types=1);

namespace App\Domain\Image\Jobs;

use App\Domain\Image\Jobs\Middleware\RespectCloudflareRateLimit;
use App\Integrations\Cloudflare\Facades\Cloudflare;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Saloon\Exceptions\Request\Statuses\NotFoundException;

class DeleteCloudflareImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 0;

    public int $maxExceptions = 3;

    public int $backoff = 60;

    public function __construct(public string $imageId)
    {
        $this->onQueue(ImportImageJob::QUEUE);
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
        // A database cloned from another environment holds that environment's ids.
        if (! str_starts_with($this->imageId, config('cloudflare.id_prefix').'-')) {
            Log::warning('Refused to delete Cloudflare image from another environment.', [
                'image_id' => $this->imageId,
            ]);

            return;
        }

        try {
            Cloudflare::images()->delete($this->imageId);
        } catch (NotFoundException) {
            return;
        }
    }
}
