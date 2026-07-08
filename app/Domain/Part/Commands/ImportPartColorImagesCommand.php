<?php

declare(strict_types=1);

namespace App\Domain\Part\Commands;

use App\Domain\Part\Jobs\FullImportPartColorImagesJob;
use App\Domain\Part\Jobs\ImportPartColorImageFromDefinitionJob;
use App\Models\Pivots\PartColor;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

#[Signature('part:import-color-images {--full} {--definition=}')]
class ImportPartColorImagesCommand extends Command
{
    public function handle(): void
    {
        if ($this->option('full')) {
            FullImportPartColorImagesJob::dispatchSync();

            return;
        }

        $this->retrieveEligiblePartColors($this->option('definition'))
            ->each(fn (PartColor $partColor) => ImportPartColorImageFromDefinitionJob::dispatch($partColor->id));
    }

    /**
     * Part colors that still need a Bricqer image: those with a definition id
     * but no image imported yet. Optionally narrowed to a single definition id.
     *
     * @return LazyCollection<int, PartColor>
     */
    protected function retrieveEligiblePartColors(?string $definitionId = null): LazyCollection
    {
        return PartColor::query()
            ->whereNotNull('bricqer_definition_id')
            ->whereNull('bricqer_image_url')
            ->when($definitionId, fn ($query) => $query->where('bricqer_definition_id', $definitionId))
            ->lazyById();
    }
}
