<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations\Bricqer;

use App\Integrations\Bricqer\Facades\Bricqer;
use App\Integrations\Bricqer\Requests\Definition\GetDefinitionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class GetDefinitionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'bricqer.domain' => 'test.bricqer.com',
            'bricqer.api_key' => 'test-key',
        ]);
    }

    public function test_it_maps_single_definition_responses_that_omit_id_and_total_remaining_quantity(): void
    {
        // Shape returned by GET /definitions/lego/definition/{id} (differs from list).
        Saloon::fake([
            GetDefinitionRequest::class => MockResponse::make([
                'legoType' => 'M',
                'legoId' => 'sw1085',
                'legoIdFull' => 'Minifig sw1085',
                'picture' => 'https://cdn.bricqer.com/static/bl-cache/MN/0/sw1085.png',
                'legoCategoryId' => 47,
                'pictureCount' => null,
                'comment' => null,
                'eanNumber' => null,
                'completeness' => null,
                'weight' => 4.4,
                'description' => 'R2-D2',
                'pgAvgPrice' => 2.12,
                'pgStr' => 0.68,
                'condition' => 'U',
                'color' => null,
                'price' => 2.562,
                'minPrice' => null,
                'priceUpdated' => '2025-10-15',
                'priceOverrides' => [],
                'priceLocked' => false,
                'salePercent' => null,
                'bulkQty' => 1,
                'requiresComment' => false,
                'lastSold' => '2025-10-24T20:17:40.802683Z',
                'soldQty' => 0,
                'remainingQuantity' => null,
            ]),
        ]);

        $definition = Bricqer::definition()->get('10003');

        $this->assertSame(10003, $definition->id);
        $this->assertSame('M', $definition->legoType);
        $this->assertSame('sw1085', $definition->legoId);
        $this->assertSame(
            'https://cdn.bricqer.com/static/bl-cache/MN/0/sw1085.png',
            $definition->picture,
        );
        $this->assertSame(0, $definition->totalRemainingQuantity);
    }
}
