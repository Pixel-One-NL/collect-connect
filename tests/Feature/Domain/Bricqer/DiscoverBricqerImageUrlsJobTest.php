<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Bricqer;

use App\Domain\Bricqer\Jobs\AttachBricqerImagesJob;
use App\Domain\Bricqer\Jobs\DiscoverBricqerImageUrlsJob;
use App\Integrations\Bricqer\BricqerApi;
use App\Integrations\Bricqer\Requests\LegoVisual\ListItemsRequest;
use App\Models\Color;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class DiscoverBricqerImageUrlsJobTest extends TestCase
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

        $this->color = Color::factory()->create();
    }

    /**
     * A part that is actually for sale: the walk only runs for parts with at
     * least one product.
     */
    private function salablePart(string $bricklinkId, ?string $imageUrl = null): Part
    {
        $part = Part::factory()->create([
            'bricklink_id' => $bricklinkId,
            'bricqer_img_url' => $imageUrl,
        ]);

        Product::factory()->create([
            'productable_type' => $part->getMorphClass(),
            'productable_id' => $part->id,
            'color_id' => $this->color->id,
        ]);

        return $part;
    }

    /**
     * Build a single lego-visual page response body.
     *
     * @param  list<array<string, mixed>>  $results
     */
    private function page(int $number, array $results, ?string $next): array
    {
        return [
            'page' => [
                'number' => $number,
                'size' => 100,
                'count' => 953,
                'links' => ['next' => $next],
            ],
            'results' => $results,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function item(string $partNumber, ?string $image, array $overrides = []): array
    {
        return array_merge([
            'id' => fake()->unique()->numberBetween(1, 1_000_000),
            'partType' => 'P',
            'partNumber' => $partNumber,
            'partName' => 'Some Part',
            'defaultColorId' => 5,
            'image' => $image,
        ], $overrides);
    }

    /**
     * Fake the listing so every page up to $lastPage returns the given items
     * and pages beyond it 404 (how Bricqer reports past-the-end pages).
     *
     * @param  list<array<string, mixed>>  $results
     */
    private function fakeListing(array $results, int $lastPage = 1): void
    {
        Saloon::fake([
            ListItemsRequest::class => function (PendingRequest $request) use ($results, $lastPage): MockResponse {
                $page = (int) $request->query()->get('page', 1);

                if ($page > $lastPage) {
                    return MockResponse::make(['detail' => 'Invalid page.'], status: 404);
                }

                $next = $page < $lastPage
                    ? 'https://test.bricqer.com/api/v1/lego-visual/item?part_type=P&page='.($page + 1)
                    : null;

                return MockResponse::make($this->page($page, $results, $next));
            },
        ]);
    }

    private function runJob(int $startPage = 1): void
    {
        (new DiscoverBricqerImageUrlsJob($startPage))->handle(app(BricqerApi::class));
    }

    public function test_it_sets_bricqer_img_url_on_matched_parts(): void
    {
        Bus::fake();

        $part = $this->salablePart('3001');

        $this->fakeListing([
            $this->item('3001', 'https://cdn.bricqer.com/static/bl-cache/PN/0/3001.png'),
        ]);

        $this->runJob();

        $this->assertSame(
            'https://cdn.bricqer.com/static/bl-cache/PN/0/3001.png',
            $part->refresh()->bricqer_img_url,
        );
    }

    public function test_it_matches_bricklink_id_case_insensitively(): void
    {
        Bus::fake();

        $part = $this->salablePart('BL3001a');

        $this->fakeListing([
            $this->item('bl3001A', 'https://cdn.bricqer.com/img/x.png'),
        ]);

        $this->runJob();

        $this->assertSame('https://cdn.bricqer.com/img/x.png', $part->refresh()->bricqer_img_url);
    }

    public function test_it_ignores_unmatched_rows_and_rows_without_an_image(): void
    {
        Bus::fake();

        $matched = $this->salablePart('3001');
        $noImage = $this->salablePart('3002');

        $this->fakeListing([
            $this->item('3001', 'https://cdn.bricqer.com/img/3001.png'),
            $this->item('3002', null), // no image -> skipped
            $this->item('9999', 'https://cdn.bricqer.com/img/9999.png'), // unknown part
        ]);

        $this->runJob();

        $this->assertSame('https://cdn.bricqer.com/img/3001.png', $matched->refresh()->bricqer_img_url);
        $this->assertNull($noImage->refresh()->bricqer_img_url);
    }

    public function test_it_skips_the_walk_when_no_salable_part_is_missing_a_url(): void
    {
        Bus::fake();

        $this->salablePart('3001', imageUrl: 'https://cdn.bricqer.com/img/3001.png');
        // A part without products must not trigger a walk either.
        Part::factory()->create(['bricklink_id' => '3002', 'bricqer_img_url' => null]);

        Saloon::fake([]);

        $this->runJob();

        Saloon::assertNothingSent();
    }

    public function test_it_dispatches_the_next_window_while_parts_are_still_missing_a_url(): void
    {
        Bus::fake();

        $this->salablePart('3001');

        // 100 pages of rows that never match the part -> the first window (20
        // pages) ends with the part still missing its url.
        $this->fakeListing([
            $this->item('9999', 'https://cdn.bricqer.com/img/9999.png'),
        ], lastPage: 100);

        $this->runJob();

        Bus::assertDispatched(DiscoverBricqerImageUrlsJob::class, function (DiscoverBricqerImageUrlsJob $job): bool {
            return $job->startPage === 21;
        });
    }

    public function test_it_stops_the_walk_once_every_salable_part_has_a_url(): void
    {
        Bus::fake();

        $this->salablePart('3001');

        // The listing would continue, but the only missing part is matched on
        // the first window -> no further windows.
        $this->fakeListing([
            $this->item('3001', 'https://cdn.bricqer.com/img/3001.png'),
        ], lastPage: 100);

        $this->runJob();

        Bus::assertNotDispatched(DiscoverBricqerImageUrlsJob::class);
    }

    public function test_it_treats_a_past_the_end_page_as_the_end_of_the_walk(): void
    {
        Bus::fake();

        $part = $this->salablePart('3001');
        $this->salablePart('4444'); // never appears in the listing

        // Only 2 real pages; the rest of the first window 404s.
        $this->fakeListing([
            $this->item('3001', 'https://cdn.bricqer.com/img/3001.png'),
        ], lastPage: 2);

        $this->runJob();

        $this->assertSame('https://cdn.bricqer.com/img/3001.png', $part->refresh()->bricqer_img_url);
        Bus::assertNotDispatched(DiscoverBricqerImageUrlsJob::class);
    }

    public function test_it_hands_over_to_the_attach_fan_out_when_the_walk_finishes(): void
    {
        Bus::fake();

        $part = $this->salablePart('3001');
        $part->partColors()->create(['part_id' => $part->id, 'color_id' => $this->color->id]);

        $this->fakeListing([
            $this->item('3001', 'https://cdn.bricqer.com/img/3001.png'),
        ]);

        $this->runJob();

        Bus::assertBatched(function (PendingBatch $batch): bool {
            return collect($batch->jobs)->every(fn ($job): bool => $job instanceof AttachBricqerImagesJob);
        });
    }
}
