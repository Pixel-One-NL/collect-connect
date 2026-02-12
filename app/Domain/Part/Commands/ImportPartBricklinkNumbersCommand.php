<?php

declare(strict_types=1);

namespace App\Domain\Part\Commands;

use App\Domain\Part\Jobs\ImportPartBricklinkNumbersJob;
use Illuminate\Console\Command;

class ImportPartBricklinkNumbersCommand extends Command
{
    protected $signature = 'part:import-bricklink-ids {--filter=}';

    public function handle(): void
    {
        $filter = is_string($this->option('filter')) && ! empty($this->option('filter'))
            ? $this->option('filter')
            : null;

        ImportPartBricklinkNumbersJob::dispatch($filter);
    }
}
