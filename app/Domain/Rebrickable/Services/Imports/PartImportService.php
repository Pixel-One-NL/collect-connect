<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services\Imports;

use App\Domain\Rebrickable\Mappers\Imports\PartImportMapper;
use App\Domain\Rebrickable\Services\BaseImportService;
use App\Models\Part;

class PartImportService extends BaseImportService
{
    public function getUrl(): string
    {
        return 'https://cdn.rebrickable.com/media/downloads/parts.csv.zip';
    }

    public function getModel(): string
    {
        return Part::class;
    }

    public function getMapper(): string
    {
        return PartImportMapper::class;
    }
}
