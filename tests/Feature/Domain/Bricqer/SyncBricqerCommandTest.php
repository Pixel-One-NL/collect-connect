<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Bricqer;

use App\Domain\Bricqer\Jobs\SyncBricqerInventoryJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SyncBricqerCommandTest extends TestCase
{
    public function test_it_queues_the_sync_job(): void
    {
        Queue::fake();

        $this->assertSame(0, Artisan::call('bricqer:sync'));

        Queue::assertPushed(SyncBricqerInventoryJob::class);
    }
}
