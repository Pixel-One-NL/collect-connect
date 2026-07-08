<?php

declare(strict_types=1);

namespace App\Domain\Set\Jobs;

use App\Models\Set;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class ImportSetImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public int $setId,
        public string $imageUrl,
    ) {}

    public function handle(): void
    {
        $set = Set::query()->find($this->setId);

        if (! $set) {
            return;
        }

        if ($set->hasMedia(Set::IMAGE_COLLECTION)) {
            return;
        }

        try {
            $set
                ->addMediaFromUrl($this->imageUrl)
                ->toMediaCollection(Set::IMAGE_COLLECTION);
        } catch (UnreachableUrl $exception) {
            return;
        }
    }
}
