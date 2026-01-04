<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class PartNameTextColumn
{
    public static function make(?string $name = 'name'): TextColumn
    {
        return TextColumn::make($name);
    }
}
