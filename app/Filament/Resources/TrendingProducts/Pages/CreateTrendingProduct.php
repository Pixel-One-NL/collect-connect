<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrendingProducts\Pages;

use App\Filament\Resources\TrendingProducts\TrendingProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrendingProduct extends CreateRecord
{
    protected static string $resource = TrendingProductResource::class;
}
