<?php

declare(strict_types=1);

namespace App\Domain\Minifig\Jobs;

use App\Models\Minifig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class ImportMinifigImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public int $minifigId,
        public string $imageUrl,
    ) {}

    public function handle(): void
    {
        $minifig = Minifig::query()->find($this->minifigId);

        if (! $minifig) {
            return;
        }

        if ($minifig->hasMedia(Minifig::BRICQER_IMAGE_COLLECTION)) {
            return;
        }

        try {
            $minifig
                ->addMediaFromUrl($this->imageUrl)
                ->toMediaCollection(Minifig::BRICQER_IMAGE_COLLECTION);

            $minifig->update(['bricqer_image_url' => $this->imageUrl]);
        } catch (UnreachableUrl) {
            return;
        }
    }
}
