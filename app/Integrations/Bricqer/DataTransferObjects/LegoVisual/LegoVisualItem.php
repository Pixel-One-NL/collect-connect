<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\DataTransferObjects\LegoVisual;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class LegoVisualItem extends Data
{
    public int $id;

    #[MapName('partType')]
    public string $partType;

    #[MapName('partNumber')]
    public string $partNumber;

    #[MapName('partName')]
    public ?string $partName;

    #[MapName('defaultColorId')]
    public ?int $defaultColorId;

    public ?string $image;
}
