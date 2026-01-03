<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services;

use App\Domain\Rebrickable\Contracts\ImportsRebrickableEntity;
use App\Domain\Rebrickable\Contracts\RebrickableDownloader;

abstract class BaseImportService implements ImportsRebrickableEntity
{
    protected int $batchSize = 1000;

    protected int $processedCount = 0;

    protected int $skippedCount = 0;

    public function import(): void
    {
        // TODO: Implement the import logic here, utilizing the downloader to fetch data and the mapper to transform it
        // app(RebrickableDownloader::class)->retrieveRebrickableDataFromUrl($this->getUrl());
    }
}
