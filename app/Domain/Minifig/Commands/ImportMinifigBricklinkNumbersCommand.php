<?php

declare(strict_types=1);

namespace App\Domain\Minifig\Commands;

use App\Domain\Minifig\Jobs\ImportMinifigBricklinkNumbersJob;
use Illuminate\Console\Command;

class ImportMinifigBricklinkNumbersCommand extends Command
{
    protected $signature = 'minifig:import-bricklink-ids {--filter=}';

    public function handle(): void
    {
        $filter = is_string($this->option('filter')) && ! empty($this->option('filter'))
            ? $this->option('filter')
            : null;

        ImportMinifigBricklinkNumbersJob::dispatch($filter);
    }
}
