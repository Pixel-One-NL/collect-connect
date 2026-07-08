<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\DataTransferObjects\Definition;

use Spatie\LaravelData\Data;

class DefinitionColor extends Data
{
    public int $id;

    public string $rgb;

    public string $name;

    public int $blid;
}
