<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\RelationManagers;

use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Parts\RelationManagers\Columns\InventoryPartsQuantityTextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class InventoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';

    protected static ?string $relatedResource = InventoryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->pushColumns([
                InventoryPartsQuantityTextColumn::make(),
            ]);
    }
}
