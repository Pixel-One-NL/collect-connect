<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\DataTransferObjects\LegoVisual;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class LegoVisualPage extends Data
{
    /**
     * @param  DataCollection<int, LegoVisualItem>  $results
     */
    public function __construct(
        public int $number,
        public int $size,
        public int $count,
        public ?string $nextUrl,
        public DataCollection $results,
    ) {}

    /**
     * Build the page DTO from the decoded `lego-visual/item` response.
     *
     * The pagination metadata is nested under a `page` key (with the next-page
     * link at `page.links.next`); the items live under `results`. We map it
     * explicitly rather than relying on nested attribute magic so the contract
     * stays obvious.
     *
     * @param  array<string, mixed>  $json
     */
    public static function fromResponse(array $json): self
    {
        /** @var array<int, array<string, mixed>> $results */
        $results = $json['results'] ?? [];

        /** @var DataCollection<int, LegoVisualItem> $items */
        $items = LegoVisualItem::collect($results, DataCollection::class);

        return new self(
            number: (int) data_get($json, 'page.number', 1),
            size: (int) data_get($json, 'page.size', 0),
            count: (int) data_get($json, 'page.count', 0),
            nextUrl: data_get($json, 'page.links.next'),
            results: $items,
        );
    }
}
