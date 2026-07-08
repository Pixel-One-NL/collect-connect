<?php

declare(strict_types=1);

namespace App\Filament\Resources\Minifigs\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class MinifigNameTextColumn
{
    public static function make(?string $name = 'name'): TextColumn
    {
        return TextColumn::make($name);
    }
}
