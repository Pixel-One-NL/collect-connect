<?php

declare(strict_types=1);

namespace App\Domain\Part\Jobs;

use App\Integrations\Bricqer\Facades\Bricqer;
use App\Models\Pivots\PartColor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\LazyCollection;

class FullImportPartColorImagesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        Bricqer::definition()->list()
            ->chunk(100)
            ->each(function (LazyCollection $definitions): void {
                $partColors = PartColor::query()
                    ->whereNull('bricqer_image_url')
                    ->whereIn('bricqer_definition_id', $definitions->pluck('id'))
                    ->get();

                $partColors->each(function (PartColor $partColor) use ($definitions): void {
                    $definition = $definitions->firstWhere('id', $partColor->bricqer_definition_id);

                    if (! $definition || ! $definition->picture) {
                        return;
                    }

                    $partColor->update(['bricqer_image_url' => $definition->picture]);
                    ImportPartColorImageJob::dispatch($partColor->id, $definition->picture);
                });
            });
    }
}
