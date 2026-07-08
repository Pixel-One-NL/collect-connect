<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\DataTransferObjects\Definition;

use Spatie\LaravelData\Data;

class Definition extends Data
{
    /**
     * @param  array<string, mixed>  $priceOverrides
     */
    public function __construct(
        public int $id,
        public string $legoType,
        public string $legoId,
        public string $legoIdFull,
        public ?string $picture,
        public ?int $legoCategoryId,
        public ?string $comment,
        public ?string $eanNumber,
        public ?string $completeness,
        public ?float $weight,
        public ?string $description,
        public string $condition,
        public ?DefinitionColor $color,
        public ?float $price,
        public ?float $minPrice,
        public array $priceOverrides,
        public bool $priceLocked,
        public ?float $salePercent,
        public int $bulkQty,
        public bool $requiresComment,
        public int $totalRemainingQuantity,
    ) {}
}
