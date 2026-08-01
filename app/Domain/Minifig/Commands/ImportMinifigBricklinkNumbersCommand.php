<?php

declare(strict_types=1);

namespace App\Domain\Minifig\Commands;

use App\Domain\Minifig\Jobs\ImportMinifigBricklinkNumbersJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class ImportMinifigBricklinkNumbersCommand extends Command
{
    protected $signature = 'minifig:import-bricklink-ids
                            {--filter= : Only import rows whose rebrickable or bricklink id contains this string}
                            {--file= : Path to a local CSV file instead of downloading}';

    protected $description = 'Import BrickLink IDs for minifigs from the remote CSV database or a local file';

    public function handle(): int
    {
        $filter = is_string($this->option('filter')) && $this->option('filter') !== ''
            ? $this->option('filter')
            : null;

        $file = is_string($this->option('file')) && $this->option('file') !== ''
            ? $this->option('file')
            : null;

        if ($file !== null && ! File::exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        if ($file === null) {
            $this->warn('No --file given; downloading from '.config('minifig.bricklink_number_database_url'));
        } else {
            $this->info("Importing from {$file}");
        }

        try {
            $stats = (new ImportMinifigBricklinkNumbersJob(
                filter: $filter,
                file: $file,
            ))->handle();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Done: %d CSV rows with BrickLink ids, %d matched minifigs, %d updated, %d CSV ids with no local minifig, %d skipped (empty).',
            $stats['rows'],
            $stats['matched'],
            $stats['updated'],
            $stats['not_found'],
            $stats['skipped_empty'],
        ));

        if ($stats['rows'] === 0) {
            $this->error('No rows were imported. Check the CSV source.');

            return self::FAILURE;
        }

        if ($stats['matched'] === 0) {
            $this->warn('No CSV ids matched local minifigs.rebrickable_id.');
        } elseif ($stats['updated'] === 0) {
            $this->comment('All matched minifigs already had the same BrickLink id (nothing to change).');
        }

        return self::SUCCESS;
    }
}
