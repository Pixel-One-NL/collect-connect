<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\DataTransferObjects;

use App\Integrations\Rebrickable\BaseDataTransferObject;
use Spatie\LaravelData\Attributes\MapName;

class PartColor extends BaseDataTransferObject
{
    #[MapName('color_id')]
    public int $colorId;

    #[MapName('color_name')]
    public string $colorName;

    #[MapName('part_img_url')]
    public ?string $partImageUrl;
}
