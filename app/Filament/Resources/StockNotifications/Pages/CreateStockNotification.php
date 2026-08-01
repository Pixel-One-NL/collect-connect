<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockNotifications\Pages;

use App\Filament\Resources\StockNotifications\StockNotificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockNotification extends CreateRecord
{
    protected static string $resource = StockNotificationResource::class;
}
