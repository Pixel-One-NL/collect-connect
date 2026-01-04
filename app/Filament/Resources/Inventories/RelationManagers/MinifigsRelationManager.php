<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventories\RelationManagers;

use App\Filament\Resources\Minifigs\MinifigResource;
use App\Filament\Resources\Minifigs\RelationManagers\Columns\InventoryMinifigsQuantityTextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MinifigsRelationManager extends RelationManager
{
    protected static string $relationship = 'minifigs';

    protected static ?string $relatedResource = MinifigResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->pushColumns([
                InventoryMinifigsQuantityTextColumn::make(),
            ]);
    }
}
