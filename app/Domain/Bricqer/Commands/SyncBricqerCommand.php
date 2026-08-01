<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Commands;

use App\Domain\Bricqer\Jobs\SyncBricqerInventoryJob;
use Illuminate\Console\Command;

class SyncBricqerCommand extends Command
{
    protected $signature = 'bricqer:sync';

    protected $description = 'Queue a full Bricqer inventory CSV sync into products.';

    public function handle(): int
    {
        SyncBricqerInventoryJob::dispatch();

        $this->info('Queued the Bricqer sync job');

        return self::SUCCESS;
    }
}
