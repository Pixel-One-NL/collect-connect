<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\Tables;

use App\Filament\Resources\Parts\Tables\Columns\PartCategoryNameTextColumn;
use App\Filament\Resources\Parts\Tables\Columns\PartNameTextColumn;
use App\Filament\Resources\Parts\Tables\Filters\PartCategorySelectFilter;
use Filament\Tables\Table;

class PartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                PartNameTextColumn::make(),
                PartCategoryNameTextColumn::make(),
            ])
            ->filters([
                PartCategorySelectFilter::make(),
            ]);
    }
}
