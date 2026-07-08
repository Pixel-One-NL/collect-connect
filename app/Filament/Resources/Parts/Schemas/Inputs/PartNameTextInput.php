<?php

declare(strict_types=1);

namespace App\Filament\Resources\Parts\Schemas\Inputs;

use Filament\Forms\Components\TextInput;

class PartNameTextInput
{
    public static function make(?string $name = 'name'): TextInput
    {
        return TextInput::make($name);
    }
}
