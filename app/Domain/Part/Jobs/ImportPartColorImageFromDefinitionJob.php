<?php

declare(strict_types=1);

namespace App\Domain\Part\Jobs;

use App\Domain\Image\Jobs\ImportImageJob;
use App\Integrations\Bricqer\Facades\Bricqer;
use App\Models\Pivots\PartColor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ImportPartColorImageFromDefinitionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $partColorId) {}

    public function handle(): void
    {
        $partColor = PartColor::query()->find($this->partColorId);

        if (! $partColor || $partColor->cloudflare_image_id !== null) {
            return;
        }

        $definition = Bricqer::definition()->get($partColor->bricqer_definition_id);

        if (! $definition->picture) {
            return;
        }

        ImportImageJob::dispatch(PartColor::class, $partColor->id, $definition->picture);
    }
}
