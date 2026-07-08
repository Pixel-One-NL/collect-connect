<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Part;

use App\Domain\Part\Jobs\ImportPartBricklinkNumbersJob;
use App\Integrations\Rebrickable\Requests\Parts\ListPartsRequest;
use App\Models\Part;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class ImportPartBricklinkNumbersJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['rebrickable.key' => 'test-key']);
    }

    /**
     * Fake the Rebrickable parts list with a paginated payload built from the
     * given part rows.
     *
     * @param  array<int, array{part_num: string, bricklink?: ?string, ldraw?: ?string, img?: ?string}>  $parts
     */
    private function fakeParts(array $parts): void
    {
        $results = array_map(function (array $part): array {
            $externalIds = [];

            if (array_key_exists('bricklink', $part) && $part['bricklink'] !== null) {
                $externalIds['BrickLink'] = [$part['bricklink']];
            }

            if (array_key_exists('ldraw', $part) && $part['ldraw'] !== null) {
                $externalIds['LDraw'] = [$part['ldraw']];
            }

            return [
                'part_num' => $part['part_num'],
                'name' => 'Part '.$part['part_num'],
                'part_cat_id' => 1,
                'part_url' => 'https://example.test/'.$part['part_num'],
                'part_img_url' => $part['img'] ?? null,
                'external_ids' => $externalIds,
            ];
        }, $parts);

        Saloon::fake([
            ListPartsRequest::class => MockResponse::make([
                'count' => count($results),
                'next' => null,
                'previous' => null,
                'results' => $results,
            ]),
        ]);
    }

    public function test_it_imports_both_bricklink_and_ldraw_ids(): void
    {
        $part = Part::factory()->create([
            'rebrickable_id' => '3001',
            'bricklink_id' => null,
            'ldraw_id' => null,
        ]);

        $this->fakeParts([
            ['part_num' => '3001', 'bricklink' => '3001bl', 'ldraw' => '3001ld', 'img' => 'https://img.test/3001.png'],
        ]);

        (new ImportPartBricklinkNumbersJob)->handle();

        $part->refresh();
        $this->assertSame('3001bl', $part->bricklink_id);
        $this->assertSame('3001ld', $part->ldraw_id);
        $this->assertSame('https://img.test/3001.png', $part->rebrickable_img_url);
    }

    public function test_it_picks_up_parts_missing_only_the_ldraw_id(): void
    {
        // This part already has a bricklink id but no ldraw id, so it must be
        // re-selected for the backfill.
        $part = Part::factory()->create([
            'rebrickable_id' => '3002',
            'bricklink_id' => 'existing-bl',
            'ldraw_id' => null,
        ]);

        $this->fakeParts([
            ['part_num' => '3002', 'bricklink' => '3002bl', 'ldraw' => '3002ld'],
        ]);

        (new ImportPartBricklinkNumbersJob)->handle();

        $part->refresh();
        $this->assertSame('3002bl', $part->bricklink_id);
        $this->assertSame('3002ld', $part->ldraw_id);
    }

    public function test_it_does_not_reprocess_parts_with_both_ids_set(): void
    {
        // Fully populated part sits below the resume cursor and is skipped.
        Part::factory()->create([
            'rebrickable_id' => '3003',
            'bricklink_id' => 'bl',
            'ldraw_id' => 'ld',
        ]);

        $pending = Part::factory()->create([
            'rebrickable_id' => '3004',
            'bricklink_id' => null,
            'ldraw_id' => null,
        ]);

        $this->fakeParts([
            ['part_num' => '3004', 'bricklink' => '3004bl', 'ldraw' => '3004ld'],
        ]);

        (new ImportPartBricklinkNumbersJob)->handle();

        Saloon::assertSent(function (ListPartsRequest $request): bool {
            // The fully populated 3003 must not be requested; only 3004.
            return $request->partNumbers === ['3004'];
        });

        $pending->refresh();
        $this->assertSame('3004bl', $pending->bricklink_id);
        $this->assertSame('3004ld', $pending->ldraw_id);
    }
}
