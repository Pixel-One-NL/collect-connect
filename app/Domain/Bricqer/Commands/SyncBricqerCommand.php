<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Commands;

use App\Domain\Bricqer\Jobs\SyncBricqerInventoryJob;
use Illuminate\Console\Command;

class SyncBricqerCommand extends Command
{
    protected $signature = 'bricqer:sync';

    protected $description = 'Sync the Bricqer inventory into products and chain the catalog image import.';

    public function handle(): void
    {
        SyncBricqerInventoryJob::dispatchSync();

        $this->info('Queued the Bricqer sync job');
    }
}
