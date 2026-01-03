<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services;

use App\Domain\Rebrickable\Contracts\ImportsRebrickableEntity;
use App\Domain\Rebrickable\Contracts\RebrickableDownloader;
use App\Domain\Rebrickable\Contracts\RebrickableMapper;

abstract class BaseImportService implements ImportsRebrickableEntity
{
    /**
     * @var int<1, max>
     */
    protected int $batchSize = 1000;

    protected int $processedCount = 0;

    protected int $skippedCount = 0;

    protected RebrickableMapper $mapper;

    public function __construct()
    {
        $this->mapper = new ($this->getMapper());
    }

    public function import(): void
    {
        $rows = [];
        $data = app(RebrickableDownloader::class)->retrieveRebrickableDataFromUrl($this->getUrl());

        while ($row = array_shift($data)) {
            $rows[] = $this->mapper->map($row);
        }

        foreach (array_chunk($rows, $this->batchSize) as $chunk) {
            $this->getModel()::upsert($chunk, $this->mapper->getUniqueKey());
        }
    }
}
