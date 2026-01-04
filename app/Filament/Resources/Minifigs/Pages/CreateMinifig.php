<?php

declare(strict_types=1);

namespace App\Filament\Resources\Minifigs\Pages;

use App\Filament\Resources\Minifigs\MinifigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMinifig extends CreateRecord
{
    protected static string $resource = MinifigResource::class;
}
