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
     * @param  array<int, array{part_num: string, bricklink?: ?string}>  $parts
     */
    private function fakeParts(array $parts): void
    {
        $results = array_map(function (array $part): array {
            $externalIds = [];

            if (array_key_exists('bricklink', $part) && $part['bricklink'] !== null) {
                $externalIds['BrickLink'] = [$part['bricklink']];
            }

            return [
                'part_num' => $part['part_num'],
                'name' => 'Part '.$part['part_num'],
                'part_cat_id' => 1,
                'part_url' => 'https://example.test/'.$part['part_num'],
                'part_img_url' => null,
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

    public function test_it_imports_bricklink_ids_for_parts_missing_them(): void
    {
        $part = Part::factory()->create([
            'rebrickable_id' => '3001',
            'bricklink_id' => null,
        ]);

        $this->fakeParts([
            ['part_num' => '3001', 'bricklink' => '3001bl'],
        ]);

        (new ImportPartBricklinkNumbersJob)->handle();

        $this->assertSame('3001bl', $part->refresh()->bricklink_id);
    }

    public function test_it_skips_parts_that_already_have_a_bricklink_id(): void
    {
        Part::factory()->create([
            'rebrickable_id' => '3003',
            'bricklink_id' => 'bl',
        ]);

        $pending = Part::factory()->create([
            'rebrickable_id' => '3004',
            'bricklink_id' => null,
        ]);

        $this->fakeParts([
            ['part_num' => '3004', 'bricklink' => '3004bl'],
        ]);

        (new ImportPartBricklinkNumbersJob)->handle();

        Saloon::assertSent(function (ListPartsRequest $request): bool {
            return $request->partNumbers === ['3004'];
        });

        $this->assertSame('3004bl', $pending->refresh()->bricklink_id);
    }
}
