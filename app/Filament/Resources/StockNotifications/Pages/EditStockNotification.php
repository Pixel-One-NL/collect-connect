<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockNotifications\Pages;

use App\Filament\Resources\StockNotifications\StockNotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockNotification extends EditRecord
{
    protected static string $resource = StockNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
