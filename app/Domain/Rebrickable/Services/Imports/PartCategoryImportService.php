<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services\Imports;

use App\Domain\Rebrickable\Mappers\Imports\PartCategoryImportMapper;
use App\Domain\Rebrickable\Services\BaseImportService;
use App\Models\PartCategory;

class PartCategoryImportService extends BaseImportService
{
    public function getUrl(): string
    {
        return 'https://cdn.rebrickable.com/media/downloads/part_categories.csv.zip';
    }

    /**
     * {@inheritDoc}
     */
    public function getModel(): string
    {
        return PartCategory::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapper(): string
    {
        return PartCategoryImportMapper::class;
    }
}
