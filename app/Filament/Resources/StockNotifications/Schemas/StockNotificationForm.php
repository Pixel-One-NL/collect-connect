<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockNotifications\Schemas;

use Filament\Schemas\Schema;

class StockNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
