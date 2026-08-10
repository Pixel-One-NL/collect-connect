<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrendingProducts\Pages;

use App\Filament\Resources\TrendingProducts\TrendingProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrendingProduct extends EditRecord
{
    protected static string $resource = TrendingProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
