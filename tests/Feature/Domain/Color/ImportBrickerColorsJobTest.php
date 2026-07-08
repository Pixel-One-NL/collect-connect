<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Color;

use App\Domain\Color\Jobs\ImportBricqerColorsJob;
use App\Integrations\Bricqer\Requests\Inventory\GetColorsRequest;
use App\Models\Color;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class ImportBrickerColorsJobTest extends TestCase
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

    public function test_it_maps_bricqer_colors_onto_local_colors_by_name(): void
    {
        $red = Color::factory()->create(['name' => 'Red', 'bricker_color_id' => null]);
        $blue = Color::factory()->create(['name' => 'Blue', 'bricker_color_id' => null]);
        $green = Color::factory()->create(['name' => 'Green', 'bricker_color_id' => null]);

        $this->fakeColors([
            ['id' => 5, 'bricklink_id' => '5', 'brickowl_id' => '38', 'name' => 'Red', 'name_translated' => 'Rood', 'rgb' => 'B40000', 'is_managed' => true],
            // Case-insensitive name match.
            ['id' => 6, 'bricklink_id' => '7', 'brickowl_id' => '11', 'name' => 'BLUE', 'name_translated' => 'Blauw', 'rgb' => '0055BF', 'is_managed' => true],
            // No local color named "Magenta" -> unmatched.
            ['id' => 7, 'bricklink_id' => '71', 'brickowl_id' => '99', 'name' => 'Magenta', 'name_translated' => 'Magenta', 'rgb' => 'E4ADC8', 'is_managed' => false],
        ]);

        $stats = (new ImportBricqerColorsJob)->handle();

        $this->assertSame(3, $stats['bricqer_colors']);
        $this->assertSame(2, $stats['matched']);
        $this->assertSame(1, $stats['unmatched']);

        $this->assertSame(5, $red->refresh()->bricker_color_id);
        $this->assertSame(6, $blue->refresh()->bricker_color_id);
        $this->assertNull($green->refresh()->bricker_color_id);
    }

    /**
     * @param  list<array<string, mixed>>  $colors
     */
    private function fakeColors(array $colors): void
    {
        Saloon::fake([
            GetColorsRequest::class => MockResponse::make(body: $colors),
        ]);
    }
}
