<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Jobs;

use App\Domain\Bricqer\Services\BricqerDefinitionAttributeImporter;
use App\Integrations\Bricqer\DataTransferObjects\Definition\Definition;
use App\Integrations\Bricqer\Facades\Bricqer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Lightweight definition pass: weight + definition id only (no image download).
 * Prefer {@see \App\Domain\Part\Jobs\FullImportPartColorImagesJob} when you also
 * want images in the same Bricqer definition walk.
 */
class ImportBricqerWeightsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @return array{parts_updated: int, minifigs_updated: int, skipped: int}
     */
    public function handle(): array
    {
        $importer = new BricqerDefinitionAttributeImporter;

        /** @var Definition $definition */
        foreach (Bricqer::definition()->list() as $definition) {
            $importer->ingest($definition);
        }

        return $importer->flush();
    }
}
