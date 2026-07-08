<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Jobs;

use App\Domain\Rebrickable\Contracts\ImportsRebrickableEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ImportRebrickableEntityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  class-string<ImportsRebrickableEntity>  $importService
     */
    public function __construct(
        public string $importService,
    ) {}

    public function handle(): void
    {
        app($this->importService)->import();
    }
}
