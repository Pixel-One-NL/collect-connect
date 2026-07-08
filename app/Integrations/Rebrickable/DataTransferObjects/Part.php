<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\DataTransferObjects;

use App\Integrations\Rebrickable\BaseDataTransferObject;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapName;

class Part extends BaseDataTransferObject
{
    public string $partNum;

    public string $name;

    #[MapName('part_cat_id')]
    public int $partCategoryId;

    public string $partUrl;

    #[MapName('part_img_url')]
    public ?string $partImageUrl;

    /**
     * @var Collection<int, string> $bricklinkIds
     */
    #[MapName('external_ids.BrickLink')]
    public ?Collection $bricklinkIds;

    /**
     * @var Collection<int, string> $brickOwlIds
     */
    #[MapName('external_ids.BrickOwl')]
    public ?Collection $brickOwlIds;

    /**
     * @var Collection<int, string> $ldrawIds
     */
    #[MapName('external_ids.LDraw')]
    public ?Collection $ldrawIds;
}
