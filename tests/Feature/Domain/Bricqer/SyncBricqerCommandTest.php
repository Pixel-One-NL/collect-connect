<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Bricqer;

use App\Domain\Bricqer\Jobs\SyncBricqerInventoryJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SyncBricqerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_queues_the_sync_pipeline(): void
    {
        Queue::fake();

        $this->assertSame(0, Artisan::call('bricqer:sync'));

        Queue::assertPushed(SyncBricqerInventoryJob::class, function (SyncBricqerInventoryJob $job): bool {
            return $job->dryRun === false && $job->skipImages === false;
        });
    }

    public function test_skip_images_is_passed_to_the_job(): void
    {
        Queue::fake();

        $this->assertSame(0, Artisan::call('bricqer:sync', ['--skip-images' => true]));

        Queue::assertPushed(SyncBricqerInventoryJob::class, function (SyncBricqerInventoryJob $job): bool {
            return $job->skipImages === true;
        });
    }
}
