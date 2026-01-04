<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\Schemas;

use App\Filament\Resources\Parts\Schemas\Inputs\PartCategorySelect;
use App\Filament\Resources\Parts\Schemas\Inputs\PartNameTextInput;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                PartNameTextInput::make(),
                PartCategorySelect::make(),
            ]);
    }
}
