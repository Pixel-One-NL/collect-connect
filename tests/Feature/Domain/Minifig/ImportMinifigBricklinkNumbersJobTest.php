<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Minifig;

use App\Domain\Minifig\Jobs\ImportMinifigBricklinkNumbersJob;
use App\Models\Minifig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ImportMinifigBricklinkNumbersJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_bricklink_ids_from_a_local_file(): void
    {
        $minifig = Minifig::factory()->create([
            'rebrickable_id' => 'fig-000001',
            'bricklink_id' => null,
        ]);

        $path = storage_path('framework/testing/minifig-bricklink.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "id,bricklink,brickowl,brickset\nfig-000001,sw0001,1,sw0001\n");

        try {
            $stats = (new ImportMinifigBricklinkNumbersJob(file: $path))->handle();

            $this->assertSame('sw0001', $minifig->refresh()->bricklink_id);
            $this->assertSame(1, $stats['rows']);
            $this->assertSame(1, $stats['matched']);
            $this->assertSame(1, $stats['updated']);
        } finally {
            File::delete($path);
        }
    }

    public function test_it_imports_bricklink_ids_from_the_remote_csv_when_no_file_is_given(): void
    {
        Http::preventStrayRequests();

        $minifig = Minifig::factory()->create([
            'rebrickable_id' => 'fig-000002',
            'bricklink_id' => null,
        ]);

        Http::fake([
            config('minifig.bricklink_number_database_url') => Http::response(
                "id,bricklink\nfig-000002,sw0002\n",
                200,
            ),
        ]);

        $stats = (new ImportMinifigBricklinkNumbersJob)->handle();

        $this->assertSame('sw0002', $minifig->refresh()->bricklink_id);
        $this->assertSame(1, $stats['updated']);
    }

    public function test_it_fails_when_remote_csv_returns_an_error(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            config('minifig.bricklink_number_database_url') => Http::response('Not Found', 404),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to download');

        (new ImportMinifigBricklinkNumbersJob)->handle();
    }

    public function test_it_skips_empty_bricklink_values(): void
    {
        $minifig = Minifig::factory()->create([
            'rebrickable_id' => 'fig-000010',
            'bricklink_id' => 'keep-me',
        ]);

        $path = storage_path('framework/testing/minifig-bricklink-empty.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "id,bricklink\nfig-000010,\n");

        try {
            $stats = (new ImportMinifigBricklinkNumbersJob(file: $path))->handle();

            $this->assertSame('keep-me', $minifig->refresh()->bricklink_id);
            $this->assertSame(1, $stats['skipped_empty']);
            $this->assertSame(0, $stats['updated']);
        } finally {
            File::delete($path);
        }
    }

    public function test_it_applies_filter_to_rebrickable_or_bricklink_id(): void
    {
        $match = Minifig::factory()->create([
            'rebrickable_id' => 'fig-sw-1',
            'bricklink_id' => null,
        ]);
        $other = Minifig::factory()->create([
            'rebrickable_id' => 'fig-twn-1',
            'bricklink_id' => null,
        ]);

        $path = storage_path('framework/testing/minifig-bricklink-filter.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "id,bricklink\nfig-sw-1,sw0001\nfig-twn-1,twn001\n");

        try {
            $stats = (new ImportMinifigBricklinkNumbersJob(filter: 'sw', file: $path))->handle();

            $this->assertSame('sw0001', $match->refresh()->bricklink_id);
            $this->assertNull($other->refresh()->bricklink_id);
            $this->assertSame(1, $stats['rows']);
            $this->assertSame(1, $stats['updated']);
        } finally {
            File::delete($path);
        }
    }

    public function test_command_fails_when_file_does_not_exist(): void
    {
        $this->artisan('minifig:import-bricklink-ids', [
            '--file' => storage_path('framework/testing/missing-minifig-bricklink.csv'),
        ])
            ->expectsOutputToContain('File not found')
            ->assertFailed();
    }

    public function test_command_fails_when_remote_source_is_unavailable(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            config('minifig.bricklink_number_database_url') => Http::response('Nope', 404),
        ]);

        $this->artisan('minifig:import-bricklink-ids')
            ->expectsOutputToContain('Failed to download')
            ->assertFailed();
    }

    public function test_command_imports_from_a_local_file(): void
    {
        $minifig = Minifig::factory()->create([
            'rebrickable_id' => 'fig-000003',
            'bricklink_id' => null,
        ]);

        $path = storage_path('framework/testing/minifig-bricklink-command.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "id,bricklink\nfig-000003,sw0003\n");

        try {
            $this->artisan('minifig:import-bricklink-ids', [
                '--file' => $path,
            ])
                ->expectsOutputToContain('1 updated')
                ->assertSuccessful();

            $this->assertSame('sw0003', $minifig->refresh()->bricklink_id);
        } finally {
            File::delete($path);
        }
    }

    public function test_it_updates_many_rows_in_bulk(): void
    {
        $minifigs = collect(range(1, 25))->map(fn (int $i) => Minifig::factory()->create([
            'rebrickable_id' => sprintf('fig-%06d', $i),
            'bricklink_id' => null,
        ]));

        $lines = ['id,bricklink'];
        foreach (range(1, 25) as $i) {
            $lines[] = sprintf('fig-%06d,bl%04d', $i, $i);
        }

        $path = storage_path('framework/testing/minifig-bricklink-bulk.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, implode("\n", $lines)."\n");

        try {
            $stats = (new ImportMinifigBricklinkNumbersJob(file: $path))->handle();

            $this->assertSame(25, $stats['updated']);
            $this->assertSame('bl0001', $minifigs->first()->refresh()->bricklink_id);
            $this->assertSame('bl0025', $minifigs->last()->refresh()->bricklink_id);
        } finally {
            File::delete($path);
        }
    }
}
