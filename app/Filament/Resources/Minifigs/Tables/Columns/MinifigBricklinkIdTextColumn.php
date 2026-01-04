<?php

declare(strict_types=1);

namespace App\Filament\Resources\Minifigs\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class MinifigBricklinkIdTextColumn
{
    public static function make(?string $name = 'bricklink_id'): TextColumn
    {
        return TextColumn::make($name);
    }
}
