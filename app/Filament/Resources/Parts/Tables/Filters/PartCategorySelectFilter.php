<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\Tables\Filters;

use Filament\Tables\Filters\SelectFilter;

class PartCategorySelectFilter
{
    public static function make(?string $name = 'partCategory'): SelectFilter
    {
        return SelectFilter::make($name)
            ->relationship(
                name: 'partCategory',
                titleAttribute: 'name'
            );
    }
}
