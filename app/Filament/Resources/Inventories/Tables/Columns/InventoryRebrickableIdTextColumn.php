<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventories\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class InventoryRebrickableIdTextColumn
{
    public static function make(?string $name = 'rebrickable_id'): TextColumn
    {
        return TextColumn::make($name);
    }
}
