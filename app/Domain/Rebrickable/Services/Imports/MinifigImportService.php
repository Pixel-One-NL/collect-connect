<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services\Imports;

use App\Domain\Rebrickable\Mappers\Imports\MinifigImportMapper;
use App\Domain\Rebrickable\Services\BaseImportService;
use App\Models\Minifig;

class MinifigImportService extends BaseImportService
{
    public function getUrl(): string
    {
        return 'https://cdn.rebrickable.com/media/downloads/minifigs.csv.zip';
    }

    /**
     * {@inheritDoc}
     */
    public function getModel(): string
    {
        return Minifig::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapper(): string
    {
        return MinifigImportMapper::class;
    }
}
