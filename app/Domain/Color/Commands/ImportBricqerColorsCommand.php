<?php

declare(strict_types=1);

namespace App\Domain\Color\Commands;

use App\Domain\Color\Jobs\ImportBricqerColorsJob;
use Illuminate\Console\Command;

class ImportBricqerColorsCommand extends Command
{
    protected $signature = 'color:import-bricqer-colors';

    protected $description = 'Import Bricqer colors and map them onto local colors by name (sets bricker_color_id).';

    public function handle(): void
    {
        ImportBricqerColorsJob::dispatch();
    }
}
