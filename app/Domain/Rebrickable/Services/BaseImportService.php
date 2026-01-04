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

    protected RebrickableMapper $mapper;

    public function __construct()
    {
        $this->mapper = new ($this->getMapper());
    }

    public function import(): void
    {
        $data = app(RebrickableDownloader::class)->retrieveRebrickableDataFromUrl($this->getUrl());

        $rows = [];
        foreach ($data as $row) {
            $rows[] = $this->mapper->map($row);

            if (count($rows) >= $this->batchSize) {
                $this->upsertRows($rows, $this->mapper->getUniqueKey());
                $this->processedCount += count($rows);
                $rows = [];
            }
        }

        if (count($rows) > 0) {
            $this->upsertRows($rows, $this->mapper->getUniqueKey());
            $this->processedCount += count($rows);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  string|list<string>  $uniqueKey
     */
    protected function upsertRows(array $rows, string|array $uniqueKey): void
    {
        $this->getModel()::upsert($rows, $uniqueKey);
    }
}
