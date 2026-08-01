<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Bricqer;

use App\Domain\Bricqer\Jobs\ImportBricqerWeightsJob;
use App\Integrations\Bricqer\Requests\Definition\ListDefinitionsRequest;
use App\Models\Minifig;
use App\Models\Part;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class ImportBricqerWeightsJobTest extends TestCase
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

    public function test_it_imports_weights_and_definition_ids_for_parts_and_minifigs(): void
    {
        $part = Part::factory()->create([
            'bricklink_id' => '3001',
            'weight_grams' => null,
            'bricqer_definition_id' => null,
        ]);
        $minifig = Minifig::factory()->create([
            'bricklink_id' => 'sh0831',
            'weight_grams' => null,
            'bricqer_definition_id' => null,
        ]);

        Saloon::fake([
            ListDefinitionsRequest::class => MockResponse::make([
                'page' => [
                    'count' => 2,
                    'number' => 1,
                    'size' => 100,
                    'links' => ['next' => null, 'previous' => null],
                ],
                'results' => [
                    $this->definition(1, 'P', '3001', 2.5),
                    $this->definition(10, 'P', '3001', 2.6),
                    $this->definition(3, 'M', 'sh0831', 4.86),
                    $this->definition(4, 'P', '9999', 1.0),
                ],
            ]),
        ]);

        $stats = (new ImportBricqerWeightsJob)->handle();

        $this->assertSame(
            ['parts_updated' => 1, 'minifigs_updated' => 1, 'skipped' => 0],
            $stats,
        );

        $part->refresh();
        $minifig->refresh();

        $this->assertEqualsWithDelta(2.6, (float) $part->weight_grams, 0.0001);
        $this->assertSame('10', $part->bricqer_definition_id);
        $this->assertEqualsWithDelta(4.86, (float) $minifig->weight_grams, 0.0001);
        $this->assertSame('3', $minifig->bricqer_definition_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(int $id, string $type, string $legoId, float $weight): array
    {
        return [
            'id' => $id,
            'legoType' => $type,
            'legoId' => $legoId,
            'legoIdFull' => "{$type} {$legoId}",
            'picture' => null,
            'legoCategoryId' => 1,
            'comment' => null,
            'eanNumber' => null,
            'completeness' => null,
            'weight' => $weight,
            'description' => 'Test',
            'condition' => 'N',
            'color' => null,
            'price' => 0.1,
            'minPrice' => null,
            'priceOverrides' => [],
            'priceLocked' => false,
            'salePercent' => null,
            'bulkQty' => 1,
            'requiresComment' => false,
            'totalRemainingQuantity' => 1,
        ];
    }
}
