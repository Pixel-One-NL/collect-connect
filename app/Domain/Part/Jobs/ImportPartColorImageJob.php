<?php

declare(strict_types=1);

namespace App\Domain\Part\Jobs;

use App\Models\Pivots\PartColor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class ImportPartColorImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public int $partColorId,
        public string $imageUrl,
    ) {}

    public function handle(): void
    {
        $partColor = PartColor::query()->find($this->partColorId);

        if (! $partColor) {
            return;
        }

        if ($partColor->hasMedia(PartColor::BRICQER_IMAGE_COLLECTION)) {
            return;
        }

        try {
            $partColor
                ->addMediaFromUrl($this->imageUrl)
                ->toMediaCollection(PartColor::BRICQER_IMAGE_COLLECTION);
        } catch (UnreachableUrl $exception) {
            return;
        }
    }
}
