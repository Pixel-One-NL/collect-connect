<?php

declare(strict_types=1);

namespace App\Filament\Resources\Minifigs\Tables;

use App\Filament\Resources\Minifigs\Tables\Columns\MinifigBricklinkIdTextColumn;
use App\Filament\Resources\Minifigs\Tables\Columns\MinifigNameTextColumn;
use Filament\Tables\Table;

class MinifigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                MinifigNameTextColumn::make(),
                MinifigBricklinkIdTextColumn::make(),
            ]);
    }
}
