<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Commands;

use App\Domain\Bricqer\Jobs\ImportBricqerWeightsJob;
use Illuminate\Console\Command;

class ImportBricqerWeightsCommand extends Command
{
    protected $signature = 'bricqer:import-weights {--sync : Run the import immediately instead of queueing}';

    protected $description = 'Import part/minifig weights + definition ids from Bricqer definitions (no images). Prefer part:import-color-images --full to do weights, definition ids, and images in one pass.';

    public function handle(): int
    {
        if ($this->option('sync')) {
            $stats = (new ImportBricqerWeightsJob)->handle();
            $this->info(sprintf(
                'Updated %d parts and %d minifigs (%d non-P/M definitions skipped).',
                $stats['parts_updated'],
                $stats['minifigs_updated'],
                $stats['skipped'],
            ));

            return self::SUCCESS;
        }

        ImportBricqerWeightsJob::dispatch();
        $this->info('Queued the Bricqer definition attributes import job.');

        return self::SUCCESS;
    }
}
