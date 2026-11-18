<?php

declare(strict_types=1);

namespace App\Domain\Part\Commands;

use App\Domain\Part\Jobs\FullImportPartColorImagesJob;
use App\Domain\Part\Jobs\ImportPartColorImageFromDefinitionJob;
use App\Models\Pivots\PartColor;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

#[Signature('part:import-color-images {--full : Walk all Bricqer definitions (also imports weights + definition ids)} {--definition=} {--retry-failed : Include images whose source failed earlier}')]
class ImportPartColorImagesCommand extends Command
{
    public function handle(): int
    {
        if ($this->option('full')) {
            $stats = (new FullImportPartColorImagesJob((bool) $this->option('retry-failed')))->handle();

            $this->info(sprintf(
                'Definitions import done: %d parts, %d minifigs updated; %d part/minifig images queued; %d non-P/M skipped.',
                $stats['parts_updated'],
                $stats['minifigs_updated'],
                $stats['images_queued'],
                $stats['skipped'],
            ));

            return self::SUCCESS;
        }

        $this->retrieveEligiblePartColors($this->option('definition'), (bool) $this->option('retry-failed'))
            ->each(fn (PartColor $partColor) => ImportPartColorImageFromDefinitionJob::dispatch($partColor->id));

        return self::SUCCESS;
    }

    /**
     * Part colors that still need a Bricqer image: those with a definition id
     * but no image imported yet. Optionally narrowed to a single definition id.
     *
     * @return LazyCollection<int, PartColor>
     */
    protected function retrieveEligiblePartColors(?string $definitionId = null, bool $retryFailed = false): LazyCollection
    {
        return PartColor::query()
            ->whereNotNull('bricqer_definition_id')
            ->when($retryFailed, fn ($query) => $query->withoutCloudflareImage(), fn ($query) => $query->importable())
            ->when($definitionId, fn ($query) => $query->where('bricqer_definition_id', $definitionId))
            ->lazyById();
    }
}
