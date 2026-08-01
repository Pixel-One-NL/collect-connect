<?php

declare(strict_types=1);

namespace App\Domain\Minifig\Jobs;

use App\Integrations\Bricqer\Facades\Bricqer;
use App\Models\Minifig;
use App\Support\BricqerImageUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class ImportMinifigImageFromDefinitionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $minifigId) {}

    public function handle(): void
    {
        $minifig = Minifig::query()->find($this->minifigId);

        if (! $minifig || $minifig->hasMedia(Minifig::BRICQER_IMAGE_COLLECTION)) {
            return;
        }

        $imageUrl = $this->resolveImageUrl($minifig);

        if ($imageUrl === null) {
            return;
        }

        // Do not mark bricqer_image_url until download succeeds — otherwise failed
        // CDN hits permanently skip the minifig on later import runs.
        ImportMinifigImageJob::dispatch($minifig->id, $imageUrl);
    }

    /**
     * Prefer the picture from the Bricqer definition when we have an id;
     * otherwise fall back to the public Bricqer CDN pattern by BrickLink id.
     */
    protected function resolveImageUrl(Minifig $minifig): ?string
    {
        if ($minifig->bricqer_definition_id !== null) {
            try {
                $definition = Bricqer::definition()->get($minifig->bricqer_definition_id);

                if (filled($definition->picture)) {
                    return $definition->picture;
                }
            } catch (Throwable) {
                // Fall through to CDN URL — single-definition fetches can fail
                // for stale or non-shop definitions.
            }
        }

        if (! filled($minifig->bricklink_id)) {
            return null;
        }

        return BricqerImageUrl::minifig((string) $minifig->bricklink_id);
    }
}
