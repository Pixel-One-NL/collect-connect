<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\RelationManagers\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;

class InventoryPartsQuantityTextColumn
{
    public static function make(?string $name = 'quantity'): TextColumn
    {
        return TextColumn::make($name)
            ->suffix(new HtmlString('&nbsp;&times;'));
    }
}
