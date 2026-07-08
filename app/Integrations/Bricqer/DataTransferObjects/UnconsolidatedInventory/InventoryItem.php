<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\DataTransferObjects\UnconsolidatedInventory;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class InventoryItem extends Data
{
    #[MapName('Purchase ID')]
    public string $purchaseId;

    #[MapName('Batch ID')]
    public string $batchId;

    #[MapName('BatchItem ID')]
    public string $batchItemId;

    #[MapName('Definition ID')]
    public string $definitionId;

    #[MapName('Purchase description')]
    public string $purchaseDescription;

    #[MapName('Purchase contact')]
    public string $purchaseContact;

    #[MapName('Item Type')]
    public string $itemType;

    #[MapName('Item ID')]
    public string $itemId;

    #[MapName('Color')]
    public string $color;

    #[MapName('Color ID')]
    public string $colorId;

    #[MapName('Condition')]
    public string $condition;

    #[MapName('Completeness')]
    public string $completeness;

    #[MapName('Comments')]
    public string $comments;

    #[MapName('Original quantity')]
    public int $originalQuantity;

    #[MapName('Remaining quantity')]
    public int $remainingQuantity;

    #[MapName('Cost')]
    public float $cost;

    #[MapName('Price')]
    public float $price;

    #[MapName('Location')]
    public string $location;

    #[MapName('Description')]
    public string $description;
}
