<?php

declare(strict_types=1);

namespace App\Filament\Resources\Minifigs\Pages;

use App\Filament\Resources\Minifigs\MinifigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMinifigs extends ListRecords
{
    protected static string $resource = MinifigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
