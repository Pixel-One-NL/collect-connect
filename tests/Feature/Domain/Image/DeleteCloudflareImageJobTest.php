<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Image;

use App\Domain\Image\Jobs\DeleteCloudflareImageJob;
use App\Integrations\Cloudflare\Requests\Images\DeleteImageRequest;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Set;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class DeleteCloudflareImageJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_image_with_current_prefix(): void
    {
        $mockClient = Saloon::fake([DeleteImageRequest::class => MockResponse::make(['success' => true])]);

        (new DeleteCloudflareImageJob('testing-set-1'))->handle();

        $mockClient->assertSentCount(1);
    }

    public function test_it_refuses_image_from_another_environment(): void
    {
        $mockClient = Saloon::fake([]);

        (new DeleteCloudflareImageJob('production-set-1'))->handle();

        $mockClient->assertNothingSent();
    }

    public function test_image_already_gone_is_not_an_error(): void
    {
        $mockClient = Saloon::fake([DeleteImageRequest::class => MockResponse::make(['success' => false], 404)]);

        (new DeleteCloudflareImageJob('testing-set-1'))->handle();

        $mockClient->assertSentCount(1);
    }

    public function test_deleting_set_and_minifig_queues_image_deletion(): void
    {
        Queue::fake();

        $set = Set::factory()->create(['cloudflare_image_id' => 'testing-set-9']);
        $minifig = Minifig::factory()->create(['cloudflare_image_id' => 'testing-minifig-9']);
        $withoutImage = Set::factory()->create();

        $set->delete();
        $minifig->delete();
        $withoutImage->delete();

        Queue::assertPushed(DeleteCloudflareImageJob::class, 2);
        Queue::assertPushedOn('images', DeleteCloudflareImageJob::class);
        Queue::assertPushed(DeleteCloudflareImageJob::class, fn (DeleteCloudflareImageJob $job): bool => $job->imageId === 'testing-set-9');
        Queue::assertPushed(DeleteCloudflareImageJob::class, fn (DeleteCloudflareImageJob $job): bool => $job->imageId === 'testing-minifig-9');
    }

    public function test_deleting_part_queues_deletion_for_cascaded_part_color_images(): void
    {
        Queue::fake();

        $part = Part::factory()->create();
        PartColor::factory()->create(['part_id' => $part->id, 'cloudflare_image_id' => 'testing-part-color-9']);
        PartColor::factory()->create(['part_id' => $part->id]);

        $part->delete();

        $this->assertDatabaseCount('part_colors', 0);
        Queue::assertPushed(DeleteCloudflareImageJob::class, 1);
        Queue::assertPushed(DeleteCloudflareImageJob::class, fn (DeleteCloudflareImageJob $job): bool => $job->imageId === 'testing-part-color-9');
    }
}
