<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventories\RelationManagers;

use App\Filament\Resources\Parts\PartResource;
use App\Filament\Resources\Parts\RelationManagers\Columns\InventoryPartsQuantityTextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';

    protected static ?string $relatedResource = PartResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->pushColumns([
                InventoryPartsQuantityTextColumn::make(),
            ]);
    }
}
