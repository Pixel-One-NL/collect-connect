<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockNotifications;

use App\Filament\Resources\StockNotifications\Pages\CreateStockNotification;
use App\Filament\Resources\StockNotifications\Pages\EditStockNotification;
use App\Filament\Resources\StockNotifications\Pages\ListStockNotifications;
use App\Filament\Resources\StockNotifications\Schemas\StockNotificationForm;
use App\Filament\Resources\StockNotifications\Tables\StockNotificationsTable;
use App\Models\StockNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockNotificationResource extends Resource
{
    protected static ?string $model = StockNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StockNotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockNotificationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockNotifications::route('/'),
            'create' => CreateStockNotification::route('/create'),
            'edit' => EditStockNotification::route('/{record}/edit'),
        ];
    }
}
