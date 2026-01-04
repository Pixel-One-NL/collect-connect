<?php

declare(strict_types=1);

namespace App\Filament\Resources\Minifigs\Pages;

use App\Filament\Resources\Minifigs\MinifigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMinifig extends EditRecord
{
    protected static string $resource = MinifigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
