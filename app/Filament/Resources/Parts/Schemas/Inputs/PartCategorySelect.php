<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\Schemas\Inputs;

use Filament\Forms\Components\Select;

class PartCategorySelect
{
    public static function make(?string $name = 'partCategory'): Select
    {
        return Select::make($name)
            ->relationship(
                name: 'partCategory',
                titleAttribute: 'name'
            );
    }
}
