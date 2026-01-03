<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services\Imports;

use App\Domain\Rebrickable\Mappers\Imports\ColorImportMapper;
use App\Domain\Rebrickable\Services\BaseImportService;
use App\Models\Color;

class ColorImportService extends BaseImportService
{
    public function getUrl(): string
    {
        return 'https://cdn.rebrickable.com/media/downloads/colors.csv.zip';
    }

    /**
     * {@inheritDoc}
     */
    public function getModel(): string
    {
        return Color::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapper(): string
    {
        return ColorImportMapper::class;
    }
}
