<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Providers;

use App\Domain\Rebrickable\Contracts\RebrickableDownloader;
use App\Domain\Rebrickable\Services\RebrickableDownloadService;
use Illuminate\Support\ServiceProvider;

class RebrickableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RebrickableDownloader::class, RebrickableDownloadService::class);
    }
}
