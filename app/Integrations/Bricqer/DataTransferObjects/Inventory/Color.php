<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\DataTransferObjects\Inventory;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class Color extends Data
{
    public int $id;

    #[MapName('bricklink_id')]
    public ?string $bricklinkId;

    #[MapName('brickowl_id')]
    public ?string $brickowlId;

    public string $name;

    #[MapName('name_translated')]
    public ?string $nameTranslated;

    public ?string $rgb;

    #[MapName('is_managed')]
    public bool $isManaged;
}
