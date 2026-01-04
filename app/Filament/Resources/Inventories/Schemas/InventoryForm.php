<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventories\Schemas;

use Filament\Schemas\Schema;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
