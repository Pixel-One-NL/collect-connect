<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Part;

use App\Domain\Part\Actions\SyncPartColorsFromProducts;
use App\Integrations\Bricqer\Requests\Lego\Report\GetUnconsolidatedInventoryRequest;
use App\Models\Color;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class SyncPartColorsFromProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_part_color_for_every_part_product(): void
    {
        $part = Part::factory()->create();
        $red = Color::factory()->create();
        $blue = Color::factory()->create();

        Product::factory()->for($part, 'productable')->create(['color_id' => $red->id]);
        Product::factory()->for($part, 'productable')->create(['color_id' => $blue->id]);

        app(SyncPartColorsFromProducts::class)->execute();

        $this->assertDatabaseCount('part_colors', 2);
        $this->assertDatabaseHas('part_colors', ['part_id' => $part->id, 'color_id' => $red->id]);
        $this->assertDatabaseHas('part_colors', ['part_id' => $part->id, 'color_id' => $blue->id]);
    }

    public function test_it_is_idempotent_when_run_twice(): void
    {
        $part = Part::factory()->create();
        $color = Color::factory()->create();

        Product::factory()->for($part, 'productable')->create(['color_id' => $color->id]);

        app(SyncPartColorsFromProducts::class)->execute();
        app(SyncPartColorsFromProducts::class)->execute();

        $this->assertDatabaseCount('part_colors', 1);
    }

    public function test_it_preserves_existing_part_colors(): void
    {
        $part = Part::factory()->create();
        $color = Color::factory()->create();

        // Pre-existing pivot (e.g. created by a prior LDraw import).
        PartColor::create(['part_id' => $part->id, 'color_id' => $color->id]);

        Product::factory()->for($part, 'productable')->create(['color_id' => $color->id]);

        app(SyncPartColorsFromProducts::class)->execute();

        $this->assertDatabaseCount('part_colors', 1);
    }

    public function test_the_command_syncs_part_colors(): void
    {
        $part = Part::factory()->create();
        $color = Color::factory()->create();

        Product::factory()->for($part, 'productable')->create(['color_id' => $color->id]);

        $this->assertSame(0, Artisan::call('part-color:sync-from-products'));

        $this->assertDatabaseHas('part_colors', ['part_id' => $part->id, 'color_id' => $color->id]);
    }

    public function test_inventory_import_materializes_part_colors(): void
    {
        config([
            'bricqer.domain' => 'test.bricqer.com',
            'bricqer.api_key' => 'test-key',
        ]);

        $color = Color::factory()->create(['name' => 'Red', 'bricker_color_id' => 5]);
        $part = Part::factory()->create(['bricklink_id' => '3001']);

        Saloon::fake([
            GetUnconsolidatedInventoryRequest::class => MockResponse::make(
                body: $this->buildInventoryCsv($part->bricklink_id, '5'),
                headers: ['Content-Type' => 'text/csv'],
            ),
        ]);

        (new \App\Domain\Bricqer\Jobs\SyncBricqerInventoryJob(skipImages: true))->handle();

        $this->assertDatabaseHas('products', [
            'productable_id' => $part->id,
            'color_id' => $color->id,
        ]);
        $this->assertDatabaseHas('part_colors', [
            'part_id' => $part->id,
            'color_id' => $color->id,
        ]);
    }

    private function buildInventoryCsv(string $itemId, string $colorId): string
    {
        $columns = [
            'Purchase ID', 'Batch ID', 'BatchItem ID', 'Definition ID',
            'Purchase description', 'Purchase contact', 'Item Type', 'Item ID',
            'Color', 'Color ID', 'Condition', 'Completeness', 'Comments',
            'Original quantity', 'Remaining quantity', 'Cost', 'Price',
            'Location', 'Description',
        ];

        // Condition must be the single-letter code 'N' (new) that the import
        // job filters on.
        $row = [
            '1', '1', '1', '1', 'Test purchase', 'Test contact', 'P', $itemId,
            'Red', $colorId, 'N', 'Complete', '', '10', '5', '0.05', '0.10',
            'A1', 'Test item',
        ];

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $columns, ',', escape: '');
        fputcsv($handle, $row, ',', escape: '');
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
