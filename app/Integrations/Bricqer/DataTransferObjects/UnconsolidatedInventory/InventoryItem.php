<?php

namespace App\Integrations\Bricqer\DataTransferObjects\UnconsolidatedInventory;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class InventoryItem extends Data
{
    #[MapName('Purchase ID')]
    public readonly string $purchaseId;

    #[MapName('Batch ID')]
    public readonly string $batchId;

    #[MapName('BatchItem ID')]
    public readonly string $batchItemId;

    #[MapName('Definition ID')]
    public readonly string $definitionId;

    #[MapName('Purchase description')]
    public readonly string $purchaseDescription;

    #[MapName('Purchase contact')]
    public readonly string $purchaseContact;

    #[MapName('Item Type')]
    public readonly string $itemType;

    #[MapName('Item ID')]
    public readonly string $itemId;

    #[MapName('Color')]
    public readonly string $color;

    #[MapName('Color ID')]
    public readonly string $colorId;

    #[MapName('Condition')]
    public readonly string $condition;

    #[MapName('Completeness')]
    public readonly string $completeness;

    #[MapName('Comments')]
    public readonly string $comments;

    #[MapName('Original quantity')]
    public readonly int $originalQuantity;

    #[MapName('Remaining quantity')]
    public readonly int $remainingQuantity;

    #[MapName('Cost')]
    public readonly float $cost;

    #[MapName('Price')]
    public readonly float $price;

    #[MapName('Location')]
    public readonly string $location;

    #[MapName('Description')]
    public readonly string $description;
}
