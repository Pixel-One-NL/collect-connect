<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class PartCategoryNameTextColumn
{
    public static function make(?string $name = 'partCategory.name'): TextColumn
    {
        return TextColumn::make($name);
    }
}
