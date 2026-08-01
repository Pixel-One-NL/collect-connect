<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Bricqer;

use App\Domain\Bricqer\Jobs\SyncBricqerInventoryJob;
use App\Integrations\Bricqer\Requests\Lego\Report\GetUnconsolidatedInventoryRequest;
use App\Models\Color;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Scout\Jobs\MakeSearchable;
use RuntimeException;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class SyncBricqerInventoryJobTest extends TestCase
{
    use RefreshDatabase;

    protected Color $color;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'bricqer.domain' => 'test.bricqer.com',
            'bricqer.api_key' => 'test-key',
        ]);

        Queue::fake();

        // Matches the default CSV "Color ID" of 5 (BrickLink color id).
        $this->color = Color::factory()->create([
            'name' => 'Red',
            'bricklink_color_id' => '5',
        ]);
    }

    /**
     * @return array{
     *     found: int,
     *     color_unmatched: int,
     *     item_not_found: int,
     *     zeroed: int,
     *     minifig_definitions_updated: int,
     *     part_definitions_updated: int
     * }
     */
    private function runJob(): array
    {
        return (new SyncBricqerInventoryJob)->handle();
    }

    public function test_it_imports_parts_consolidating_stock_and_highest_price(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 5, 'Price' => 0.15],
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 3, 'Price' => 0.20],
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 2, 'Price' => 0.10],
        ]);

        $this->runJob();

        $this->assertDatabaseCount(Product::class, 1);
        $this->assertDatabaseHas(Product::class, [
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $this->color->id,
            'stock' => 10,
            'price' => 20,
        ]);
    }

    public function test_it_splits_products_by_color(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);
        $blue = Color::factory()->create([
            'name' => 'Blue',
            'bricklink_color_id' => '6',
        ]);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Color ID' => '5', 'Remaining quantity' => 5, 'Price' => 0.15],
            ['Item Type' => 'P', 'Item ID' => '3001', 'Color ID' => '6', 'Remaining quantity' => 3, 'Price' => 0.20],
        ]);

        $this->runJob();

        $this->assertDatabaseCount(Product::class, 2);
        $this->assertDatabaseHas(Product::class, [
            'productable_id' => $part->id,
            'color_id' => $this->color->id,
            'stock' => 5,
            'price' => 15,
        ]);
        $this->assertDatabaseHas(Product::class, [
            'productable_id' => $part->id,
            'color_id' => $blue->id,
            'stock' => 3,
            'price' => 20,
        ]);
    }

    public function test_it_skips_rows_whose_color_is_not_mapped(): void
    {
        Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Color ID' => '999', 'Remaining quantity' => 4, 'Price' => 0.10],
        ]);

        $stats = $this->runJob();

        $this->assertDatabaseCount(Product::class, 0);
        $this->assertSame(1, $stats['color_unmatched']);
        $this->assertSame(0, $stats['found']);
    }

    public function test_it_only_imports_items_matching_a_part(): void
    {
        Part::factory()->create(['bricklink_id' => '3023']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3023', 'Remaining quantity' => 4, 'Price' => 0.10],
            ['Item Type' => 'P', 'Item ID' => '9999', 'Remaining quantity' => 9, 'Price' => 4.99],
        ]);

        $stats = $this->runJob();

        $this->assertDatabaseCount(Product::class, 1);
        $this->assertSame(1, $stats['found']);
        $this->assertSame(1, $stats['item_not_found']);
    }

    public function test_it_imports_minifigs_with_not_applicable_color(): void
    {
        $minifig = \App\Models\Minifig::factory()->create(['bricklink_id' => 'sh0831']);
        Color::factory()->create([
            'name' => '(Not Applicable)',
            'bricklink_color_id' => '0',
        ]);

        $this->fakeInventory([
            ['Item Type' => 'M', 'Item ID' => 'sh0831', 'Color ID' => '0', 'Remaining quantity' => 2, 'Price' => 7.38],
        ]);

        $stats = $this->runJob();

        $this->assertSame(1, $stats['found']);
        $this->assertDatabaseHas(Product::class, [
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'stock' => 2,
            'price' => 738,
        ]);
    }

    public function test_it_imports_minifig_definition_ids_from_unconsolidated_inventory(): void
    {
        $minifig = \App\Models\Minifig::factory()->create([
            'bricklink_id' => 'sh0831',
            'bricqer_definition_id' => null,
        ]);
        Color::factory()->create([
            'name' => '(Not Applicable)',
            'bricklink_color_id' => '0',
        ]);

        $this->fakeInventory([
            [
                'Item Type' => 'M',
                'Item ID' => 'sh0831',
                'Color ID' => '0',
                'Definition ID' => '1806',
                'Remaining quantity' => 2,
                'Price' => 7.38,
            ],
            // Later lot with a newer definition — should win.
            [
                'Item Type' => 'M',
                'Item ID' => 'sh0831',
                'Color ID' => '0',
                'Definition ID' => '28689',
                'Remaining quantity' => 1,
                'Price' => 8.00,
            ],
        ]);

        $stats = $this->runJob();

        $this->assertSame(1, $stats['minifig_definitions_updated']);
        $this->assertSame('28689', $minifig->refresh()->bricqer_definition_id);
        $this->assertDatabaseHas(Product::class, [
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'stock' => 3,
        ]);
    }

    public function test_it_sets_minifig_definition_ids_even_when_color_is_unmapped(): void
    {
        $minifig = \App\Models\Minifig::factory()->create([
            'bricklink_id' => 'sw0001',
            'bricqer_definition_id' => null,
        ]);

        // No color "0" factory — product cannot be created, definition still should.
        $this->fakeInventory([
            [
                'Item Type' => 'M',
                'Item ID' => 'sw0001',
                'Color ID' => '0',
                'Definition ID' => '4242',
                'Remaining quantity' => 1,
                'Price' => 5.00,
            ],
        ]);

        $stats = $this->runJob();

        $this->assertSame(0, $stats['found']);
        $this->assertSame(1, $stats['color_unmatched']);
        $this->assertSame(1, $stats['minifig_definitions_updated']);
        $this->assertSame('4242', $minifig->refresh()->bricqer_definition_id);
    }

    public function test_it_does_not_regress_a_higher_definition_id(): void
    {
        $minifig = \App\Models\Minifig::factory()->create([
            'bricklink_id' => 'sw0001',
            'bricqer_definition_id' => '9000',
        ]);
        Color::factory()->create([
            'name' => '(Not Applicable)',
            'bricklink_color_id' => '0',
        ]);

        $this->fakeInventory([
            [
                'Item Type' => 'M',
                'Item ID' => 'sw0001',
                'Color ID' => '0',
                'Definition ID' => '100',
                'Remaining quantity' => 1,
                'Price' => 5.00,
            ],
        ]);

        $this->runJob();

        $this->assertSame('9000', $minifig->refresh()->bricqer_definition_id);
    }

    public function test_it_refuses_to_zero_stock_when_inventory_feed_is_empty(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);
        $product = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $this->color->id,
            'stock' => 12,
        ]);

        $this->fakeInventory([]);

        try {
            $this->runJob();
            $this->fail('Expected empty inventory feed to fail the sync.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('refusing to zero stock', $exception->getMessage());
        }

        $this->assertSame(12, $product->refresh()->stock);
    }

    public function test_it_ignores_set_item_types(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 4, 'Price' => 0.15],
            ['Item Type' => 'S', 'Item ID' => '75192', 'Remaining quantity' => 1, 'Price' => 99.99],
        ]);

        $this->runJob();

        $this->assertDatabaseCount(Product::class, 1);
        $this->assertDatabaseHas(Product::class, [
            'productable_id' => $part->id,
            'stock' => 4,
            'price' => 15,
        ]);
    }

    public function test_it_ignores_used_condition_rows(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 4, 'Price' => 0.15],
            ['Item Type' => 'P', 'Item ID' => '3001', 'Condition' => 'U', 'Remaining quantity' => 50, 'Price' => 0.05],
        ]);

        $this->runJob();

        $this->assertDatabaseHas(Product::class, [
            'productable_id' => $part->id,
            'stock' => 4,
            'price' => 15,
        ]);
    }

    public function test_it_parses_semicolon_delimited_csv(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 6, 'Price' => 0.30],
        ], delimiter: ';');

        $this->runJob();

        $this->assertDatabaseHas(Product::class, [
            'productable_id' => $part->id,
            'stock' => 6,
            'price' => 30,
        ]);
    }

    public function test_it_matches_parts_case_insensitively(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '41740stk01']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '41740STK01', 'Remaining quantity' => 3, 'Price' => 0.50],
        ]);

        $this->runJob();

        $this->assertDatabaseHas(Product::class, [
            'productable_id' => $part->id,
            'stock' => 3,
            'price' => 50,
        ]);
    }

    public function test_it_is_idempotent_when_run_twice(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        $rows = [
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 5, 'Price' => 0.15],
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 3, 'Price' => 0.20],
        ];

        $this->fakeInventory($rows);
        $this->runJob();

        $this->fakeInventory($rows);
        $this->runJob();

        $this->assertDatabaseCount(Product::class, 1);
        $this->assertDatabaseHas(Product::class, [
            'productable_id' => $part->id,
            'color_id' => $this->color->id,
            'stock' => 8,
            'price' => 20,
        ]);
    }

    public function test_it_materializes_part_colors(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Definition ID' => 'def-99', 'Remaining quantity' => 5, 'Price' => 0.15],
        ]);

        $this->runJob();

        $this->assertDatabaseHas(PartColor::class, [
            'part_id' => $part->id,
            'color_id' => $this->color->id,
            'bricqer_definition_id' => 'def-99',
        ]);
    }

    public function test_it_does_not_overwrite_part_color_definition_with_zero(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);
        PartColor::query()->create([
            'part_id' => $part->id,
            'color_id' => $this->color->id,
            'bricqer_definition_id' => '5555',
        ]);

        $this->fakeInventory([
            [
                'Item Type' => 'P',
                'Item ID' => '3001',
                'Definition ID' => '0',
                'Remaining quantity' => 2,
                'Price' => 0.10,
            ],
        ]);

        $this->runJob();

        $this->assertDatabaseHas(PartColor::class, [
            'part_id' => $part->id,
            'color_id' => $this->color->id,
            'bricqer_definition_id' => '5555',
        ]);
    }

    public function test_it_zeros_stock_for_products_missing_from_the_feed(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);
        $gone = Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $this->color->id,
            'stock' => 12,
            'price' => 50,
        ]);

        $otherPart = Part::factory()->create(['bricklink_id' => '3023']);
        $stillThere = Product::factory()->create([
            'productable_type' => $otherPart->getMorphClass(),
            'productable_id' => $otherPart->id,
            'color_id' => $this->color->id,
            'stock' => 3,
            'price' => 10,
        ]);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3023', 'Remaining quantity' => 7, 'Price' => 0.25],
        ]);

        $stats = $this->runJob();

        $this->assertSame(1, $stats['zeroed']);
        $this->assertSame(0, $gone->refresh()->stock);
        $this->assertSame(7, $stillThere->refresh()->stock);
        $this->assertSame(25, $stillThere->price);
    }

    public function test_it_queues_imported_products_for_search_indexing(): void
    {
        config(['scout.queue' => true]);

        Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 5, 'Price' => 0.15],
        ]);

        $this->runJob();

        Queue::assertPushed(MakeSearchable::class, function (MakeSearchable $job): bool {
            return $job->models->count() === 1;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function fakeInventory(array $rows, string $delimiter = ','): void
    {
        Saloon::fake([
            GetUnconsolidatedInventoryRequest::class => MockResponse::make(
                body: $this->buildCsv($rows, $delimiter),
                headers: ['Content-Type' => 'text/csv'],
            ),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function buildCsv(array $rows, string $delimiter = ','): string
    {
        $columns = [
            'Purchase ID', 'Batch ID', 'BatchItem ID', 'Definition ID',
            'Purchase description', 'Purchase contact', 'Item Type', 'Item ID',
            'Color', 'Color ID', 'Condition', 'Completeness', 'Comments',
            'Original quantity', 'Remaining quantity', 'Cost', 'Price',
            'Location', 'Description',
        ];

        $defaults = [
            'Purchase ID' => '1',
            'Batch ID' => '1',
            'BatchItem ID' => '1',
            'Definition ID' => '1',
            'Purchase description' => 'Test purchase',
            'Purchase contact' => 'Test contact',
            'Item Type' => 'P',
            'Item ID' => '3001',
            'Color' => 'Red',
            'Color ID' => '5',
            'Condition' => 'N',
            'Completeness' => 'Complete',
            'Comments' => '',
            'Original quantity' => 10,
            'Remaining quantity' => 10,
            'Cost' => 0.05,
            'Price' => 0.10,
            'Location' => 'A1',
            'Description' => 'Test item',
        ];

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $columns, $delimiter, escape: '');

        foreach ($rows as $row) {
            $row = [...$defaults, ...$row];
            fputcsv($handle, array_map(static fn (string $column): string => (string) $row[$column], $columns), $delimiter, escape: '');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv ?: '';
    }
}
