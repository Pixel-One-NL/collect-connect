<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Bricqer;

use App\Domain\Bricqer\Jobs\DiscoverBricqerImageUrlsJob;
use App\Domain\Bricqer\Jobs\SyncBricqerInventoryJob;
use App\Integrations\Bricqer\Requests\Lego\Report\GetUnconsolidatedInventoryRequest;
use App\Models\Color;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Scout\Jobs\MakeSearchable;
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

        // Matches the default CSV "Color ID" of 5.
        $this->color = Color::factory()->create(['name' => 'Red', 'bricker_color_id' => 5]);
    }

    private function runJob(bool $dryRun = false, bool $skipImages = false): array
    {
        return (new SyncBricqerInventoryJob($dryRun, $skipImages))->handle();
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
            'stock' => 10,   // 5 + 3 + 2
            'price' => 20,   // max(0.15, 0.20, 0.10) -> 0.20 EUR in cents
        ]);
    }

    public function test_it_splits_products_by_color(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);
        $blue = Color::factory()->create(['name' => 'Blue', 'bricker_color_id' => 6]);

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
            // Color ID 999 has no matching local color.
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
            // Different item id with no matching part -> counted as part_not_found.
            ['Item Type' => 'P', 'Item ID' => '9999', 'Remaining quantity' => 9, 'Price' => 4.99],
        ]);

        $stats = $this->runJob();

        $this->assertDatabaseCount(Product::class, 1);
        $this->assertSame(1, $stats['found']);
        $this->assertSame(1, $stats['part_not_found']);
    }

    public function test_it_ignores_non_part_item_types(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 4, 'Price' => 0.15],
            // Same item id, but a minifig (type M) must not affect the part.
            ['Item Type' => 'M', 'Item ID' => '3001', 'Remaining quantity' => 100, 'Price' => 9.99],
        ]);

        $this->runJob();

        $this->assertDatabaseCount(Product::class, 1);
        $this->assertDatabaseHas(Product::class, [
            'productable_id' => $part->id,
            'stock' => 4,    // only the P row
            'price' => 15,   // only the P row -> 0.15 EUR in cents
        ]);
    }

    public function test_it_ignores_used_condition_rows(): void
    {
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 4, 'Price' => 0.15],
            // Used-condition stock must not be sold as new.
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

    public function test_it_queues_imported_products_for_search_indexing(): void
    {
        // Force Scout onto the queue so the bulk searchable() call is
        // observable as a job instead of a synchronous engine update.
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

    public function test_it_chains_the_image_import(): void
    {
        Part::factory()->create(['bricklink_id' => '3001']);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 5, 'Price' => 0.15],
        ]);

        $this->runJob();

        Queue::assertPushed(DiscoverBricqerImageUrlsJob::class);
    }

    public function test_it_does_not_chain_the_image_import_when_skipped_or_dry(): void
    {
        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 5, 'Price' => 0.15],
        ]);

        $this->runJob(skipImages: true);

        $this->fakeInventory([
            ['Item Type' => 'P', 'Item ID' => '3001', 'Remaining quantity' => 5, 'Price' => 0.15],
        ]);

        $this->runJob(dryRun: true);

        Queue::assertNotPushed(DiscoverBricqerImageUrlsJob::class);
    }

    /**
     * Fake the unconsolidated inventory request with a CSV body built from the
     * given partial rows (sensible defaults fill the remaining columns).
     *
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

        return $csv;
    }
}
