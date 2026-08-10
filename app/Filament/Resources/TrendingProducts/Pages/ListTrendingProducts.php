<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrendingProducts\Pages;

use App\Filament\Resources\TrendingProducts\TrendingProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrendingProducts extends ListRecords
{
    protected static string $resource = TrendingProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
