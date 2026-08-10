<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrendingProducts;

use App\Filament\Resources\TrendingProducts\Pages\CreateTrendingProduct;
use App\Filament\Resources\TrendingProducts\Pages\EditTrendingProduct;
use App\Filament\Resources\TrendingProducts\Pages\ListTrendingProducts;
use App\Filament\Resources\TrendingProducts\Schemas\TrendingProductForm;
use App\Filament\Resources\TrendingProducts\Tables\TrendingProductsTable;
use App\Models\TrendingProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrendingProductResource extends Resource
{
    protected static ?string $model = TrendingProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static ?string $navigationLabel = 'Trending op homepage';

    protected static ?string $modelLabel = 'trending product';

    protected static ?string $pluralModelLabel = 'trending producten';

    public static function form(Schema $schema): Schema
    {
        return TrendingProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrendingProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrendingProducts::route('/'),
            'create' => CreateTrendingProduct::route('/create'),
            'edit' => EditTrendingProduct::route('/{record}/edit'),
        ];
    }
}
