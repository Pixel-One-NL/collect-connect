<?php

declare(strict_types=1);

namespace App\Domain\Image\Commands;

use App\Domain\Image\Jobs\ImportImageJob;
use App\Models\Set;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('images:retry-failed')]
class RetryFailedSetImagesCommand extends Command
{
    protected $description = 'Queue another upload for set images Cloudflare could not fetch (the weekly set import skips them)';

    public function handle(): int
    {
        $queued = 0;

        Set::query()
            ->withoutCloudflareImage()
            ->whereNotNull('cloudflare_image_failed_at')
            ->whereNotNull('img_url')
            ->where('img_url', '!=', '')
            ->lazyById()
            ->each(function (Set $set) use (&$queued): void {
                ImportImageJob::dispatch(Set::class, $set->id, (string) $set->img_url);
                $queued++;
            });

        $this->info("Queued {$queued} set image imports.");

        return self::SUCCESS;
    }
}
