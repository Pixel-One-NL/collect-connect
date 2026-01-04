<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventories\Tables;

use App\Filament\Resources\Inventories\Tables\Columns\InventoryRebrickableIdTextColumn;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                InventoryRebrickableIdTextColumn::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
