<?php

declare(strict_types=1);

namespace App\Filament\Resources\Minifigs;

use App\Filament\Resources\Minifigs\Pages\EditMinifig;
use App\Filament\Resources\Minifigs\Pages\ListMinifigs;
use App\Filament\Resources\Minifigs\RelationManagers\InventoriesRelationManager;
use App\Filament\Resources\Minifigs\Schemas\MinifigForm;
use App\Filament\Resources\Minifigs\Tables\MinifigsTable;
use App\Models\Minifig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MinifigResource extends Resource
{
    protected static ?string $model = Minifig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MinifigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MinifigsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InventoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMinifigs::route('/'),
            'edit' => EditMinifig::route('/{record}/edit'),
        ];
    }
}
