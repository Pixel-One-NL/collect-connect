<?php

declare(strict_types=1);

namespace App\Filament\Resources\Minifigs\RelationManagers\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;

class InventoryMinifigsQuantityTextColumn
{
    public static function make(?string $name = 'quantity'): TextColumn
    {
        return TextColumn::make($name)
            ->suffix(new HtmlString('&nbsp;&times;'));
    }
}
