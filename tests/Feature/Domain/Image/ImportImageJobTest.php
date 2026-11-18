<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Image;

use App\Domain\Image\Jobs\ImportImageJob;
use App\Domain\Image\Jobs\Middleware\RespectCloudflareRateLimit;
use App\Integrations\Bricqer\Requests\Definition\GetDefinitionPictureRequest;
use App\Integrations\Cloudflare\Requests\Images\GetImageRequest;
use App\Integrations\Cloudflare\Requests\Images\UploadImageFromUrlRequest;
use App\Models\Minifig;
use App\Models\Pivots\PartColor;
use App\Models\Set;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use RuntimeException;
use Saloon\Exceptions\Request\Statuses\ForbiddenException;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class ImportImageJobTest extends TestCase
{
    use RefreshDatabase;

    private const string SOURCE_URL = 'https://cdn.example.test/image.png';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cloudflare.error_codes.image_already_exists' => [1001],
            'cloudflare.error_codes.source_unreachable' => [1002],
        ]);
    }

    public function test_it_stores_the_cloudflare_id_for_every_image_model(): void
    {
        $set = Set::factory()->create();
        $minifig = Minifig::factory()->create();
        $partColor = PartColor::factory()->create();

        Saloon::fake([
            MockResponse::make(['result' => ['id' => "testing-set-{$set->id}"]]),
            MockResponse::make(['result' => ['id' => "testing-minifig-{$minifig->id}"]]),
            MockResponse::make(['result' => ['id' => "testing-part-color-{$partColor->id}"]]),
        ]);

        (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->handle();
        (new ImportImageJob(Minifig::class, $minifig->id, self::SOURCE_URL))->handle();
        (new ImportImageJob(PartColor::class, $partColor->id, self::SOURCE_URL))->handle();

        $this->assertSame("testing-set-{$set->id}", $set->refresh()->cloudflare_image_id);
        $this->assertSame("testing-minifig-{$minifig->id}", $minifig->refresh()->cloudflare_image_id);
        $this->assertSame("testing-part-color-{$partColor->id}", $partColor->refresh()->cloudflare_image_id);
    }

    public function test_upload_sends_deterministic_id_and_source_url(): void
    {
        $set = Set::factory()->create();

        $mockClient = Saloon::fake([
            UploadImageFromUrlRequest::class => MockResponse::make(['result' => ['id' => "testing-set-{$set->id}"]]),
        ]);

        (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->handle();

        $mockClient->assertSent(fn (UploadImageFromUrlRequest $request): bool => $request->body()->get('id')->value === "testing-set-{$set->id}"
            && $request->body()->get('url')->value === self::SOURCE_URL);
    }

    public function test_success_clears_previous_failure_without_search_sync_or_local_files(): void
    {
        Storage::fake('public');

        $set = Set::factory()->create(['cloudflare_image_failed_at' => now()]);
        $savedEventFired = false;
        Set::saved(function () use (&$savedEventFired): void {
            $savedEventFired = true;
        });

        Saloon::fake([MockResponse::make(['result' => ['id' => 'testing-set-1']])]);

        (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->handle();

        $this->assertNull($set->refresh()->cloudflare_image_failed_at);
        $this->assertFalse($savedEventFired);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_it_skips_when_image_already_imported_or_model_is_gone(): void
    {
        $mockClient = Saloon::fake([]);
        $set = Set::factory()->withCloudflareImage()->create();

        (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->handle();
        (new ImportImageJob(Set::class, 999999, self::SOURCE_URL))->handle();

        $mockClient->assertNothingSent();
    }

    public function test_existing_image_from_same_source_is_adopted(): void
    {
        $set = Set::factory()->create();
        $imageId = "testing-set-{$set->id}";

        Saloon::fake([
            UploadImageFromUrlRequest::class => MockResponse::make(['errors' => [['code' => 1001]]], 409),
            GetImageRequest::class => MockResponse::make(['result' => ['id' => $imageId, 'metadata' => ['source_url' => self::SOURCE_URL]]]),
        ]);

        (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->handle();

        $this->assertSame($imageId, $set->refresh()->cloudflare_image_id);
    }

    public function test_existing_image_from_other_source_is_rejected(): void
    {
        Log::spy();
        $set = Set::factory()->create();
        $imageId = "testing-set-{$set->id}";

        Saloon::fake([
            UploadImageFromUrlRequest::class => MockResponse::make(['errors' => [['code' => 1001]]], 409),
            GetImageRequest::class => MockResponse::make(['result' => ['id' => $imageId, 'metadata' => ['source_url' => 'https://other.test/x.png']]]),
        ]);

        (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->handle();

        $set->refresh();
        $this->assertNull($set->cloudflare_image_id);
        $this->assertNotNull($set->cloudflare_image_failed_at);
        Log::shouldHaveReceived('error')->once();
    }

    public function test_unreachable_source_marks_failed_without_retry(): void
    {
        Log::spy();
        $set = Set::factory()->create();

        Saloon::fake([MockResponse::make(['errors' => [['code' => 1002]]], 400)]);

        $job = (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->withFakeQueueInteractions();
        $job->handle();

        $set->refresh();
        $this->assertNull($set->cloudflare_image_id);
        $this->assertNotNull($set->cloudflare_image_failed_at);
        $job->assertNotFailed()->assertNotReleased();
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_relative_bricqer_picture_is_resolved_to_its_public_redirect_target(): void
    {
        config(['bricqer.domain' => 'test.bricqer.com', 'bricqer.api_key' => 'test-key']);

        $partColor = PartColor::factory()->create();
        $publicUrl = 'https://cdn.example.test/ldraw/72/3032.png';

        $mockClient = Saloon::fake([
            GetDefinitionPictureRequest::class => MockResponse::make('', 302, ['Location' => $publicUrl]),
            UploadImageFromUrlRequest::class => MockResponse::make(['result' => ['id' => "testing-part-color-{$partColor->id}"]]),
        ]);

        (new ImportImageJob(PartColor::class, $partColor->id, '/api/v1/lego-data/pictures/P/85/3032/'))->handle();

        $this->assertSame("testing-part-color-{$partColor->id}", $partColor->refresh()->cloudflare_image_id);

        $mockClient->assertSent(fn (mixed $request): bool => $request instanceof GetDefinitionPictureRequest
            && $request->resolveEndpoint() === '/lego-data/pictures/P/85/3032/'
            && $request->config()->get('allow_redirects') === false);

        $mockClient->assertSent(fn (mixed $request): bool => $request instanceof UploadImageFromUrlRequest
            && $request->body()->get('url')->value === $publicUrl
            && $request->body()->get('metadata')->value === json_encode(['source_url' => $publicUrl]));
    }

    public function test_absolute_source_url_does_not_call_bricqer(): void
    {
        $set = Set::factory()->create();

        $mockClient = Saloon::fake([
            UploadImageFromUrlRequest::class => MockResponse::make(['result' => ['id' => "testing-set-{$set->id}"]]),
        ]);

        (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->handle();

        $mockClient->assertNotSent(GetDefinitionPictureRequest::class);
    }

    public function test_bricqer_picture_not_found_marks_failed_without_uploading(): void
    {
        config(['bricqer.domain' => 'test.bricqer.com', 'bricqer.api_key' => 'test-key']);

        $minifig = Minifig::factory()->create();

        $mockClient = Saloon::fake([
            GetDefinitionPictureRequest::class => MockResponse::make('', 404),
        ]);

        (new ImportImageJob(Minifig::class, $minifig->id, '/api/v1/lego-data/pictures/M/0/gone/'))->handle();

        $this->assertNotNull($minifig->refresh()->cloudflare_image_failed_at);
        $mockClient->assertNotSent(UploadImageFromUrlRequest::class);
    }

    public function test_rejected_bricqer_key_fails_loudly_instead_of_marking_image_failed(): void
    {
        config(['bricqer.domain' => 'test.bricqer.com', 'bricqer.api_key' => 'test-key']);

        $minifig = Minifig::factory()->create();

        $mockClient = Saloon::fake([
            GetDefinitionPictureRequest::class => MockResponse::make('', 302, ['Location' => '/accounts/login/']),
        ]);

        try {
            (new ImportImageJob(Minifig::class, $minifig->id, '/api/v1/lego-data/pictures/M/0/sw0001/'))->handle();
            $this->fail('Expected RuntimeException.');
        } catch (RuntimeException) {
            $this->assertNull($minifig->refresh()->cloudflare_image_failed_at);
            $mockClient->assertNotSent(UploadImageFromUrlRequest::class);
        }
    }

    public function test_bad_token_fails_loudly_instead_of_marking_image_failed(): void
    {
        $set = Set::factory()->create();

        Saloon::fake([MockResponse::make(['errors' => [['code' => 10000]]], 403)]);

        try {
            (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->handle();
            $this->fail('Expected ForbiddenException.');
        } catch (ForbiddenException) {
            $this->assertNull($set->refresh()->cloudflare_image_failed_at);
        }
    }

    public function test_rate_limit_in_production_releases_job_and_pauses_following_jobs(): void
    {
        $this->app['env'] = 'production';
        $set = Set::factory()->create();

        $mockClient = Saloon::fake([MockResponse::make(['errors' => []], 429, ['Retry-After' => '120'])]);

        $job = (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->withFakeQueueInteractions();
        (new RespectCloudflareRateLimit)->handle($job, fn (ImportImageJob $job) => $job->handle());

        $job->assertReleased(delay: 120);
        $this->assertTrue(Cache::has(RespectCloudflareRateLimit::BLOCKED_UNTIL_CACHE_KEY));

        $nextJob = (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->withFakeQueueInteractions();
        (new RespectCloudflareRateLimit)->handle($nextJob, fn (ImportImageJob $job) => $job->handle());

        $nextJob->assertReleased();
        $mockClient->assertSentCount(1);
    }

    public function test_rate_limit_without_retry_after_header_waits_five_minutes(): void
    {
        $this->app['env'] = 'production';
        $set = Set::factory()->create();

        Saloon::fake([MockResponse::make(['errors' => []], 429)]);

        $job = (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->withFakeQueueInteractions();
        (new RespectCloudflareRateLimit)->handle($job, fn (ImportImageJob $job) => $job->handle());

        $job->assertReleased(delay: 300);
    }

    public function test_rate_limit_outside_production_sleeps_and_retries(): void
    {
        Sleep::fake();
        $set = Set::factory()->create();

        $mockClient = Saloon::fake([
            MockResponse::make(['errors' => []], 429, ['Retry-After' => '30']),
            MockResponse::make(['result' => ['id' => "testing-set-{$set->id}"]]),
        ]);

        $job = (new ImportImageJob(Set::class, $set->id, self::SOURCE_URL))->withFakeQueueInteractions();
        (new RespectCloudflareRateLimit)->handle($job, fn (ImportImageJob $job) => $job->handle());

        Sleep::assertSleptTimes(1);
        $job->assertNotReleased();
        $mockClient->assertSentCount(2);
        $this->assertSame("testing-set-{$set->id}", $set->refresh()->cloudflare_image_id);
    }

    public function test_job_runs_on_images_queue_bound_by_time_not_attempts(): void
    {
        Queue::fake();

        ImportImageJob::dispatch(Set::class, 1, self::SOURCE_URL);

        Queue::assertPushedOn('images', ImportImageJob::class);

        $job = new ImportImageJob(Set::class, 1, self::SOURCE_URL);
        $this->assertSame(0, $job->tries);
        $this->assertSame(3, $job->maxExceptions);
        $this->assertTrue($job->retryUntil() > now()->addDays(6));
        $this->assertInstanceOf(RespectCloudflareRateLimit::class, $job->middleware()[0]);
    }

    public function test_retry_failed_command_queues_only_failed_sets(): void
    {
        Queue::fake();

        $failed = Set::factory()->create(['img_url' => self::SOURCE_URL, 'cloudflare_image_failed_at' => now()]);
        Set::factory()->create(['img_url' => self::SOURCE_URL]);
        Set::factory()->withCloudflareImage()->create(['img_url' => self::SOURCE_URL, 'cloudflare_image_failed_at' => now()]);

        $this->artisan('images:retry-failed')
            ->expectsOutputToContain('Queued 1 set image imports.')
            ->assertSuccessful();

        Queue::assertPushed(ImportImageJob::class, 1);
        Queue::assertPushed(ImportImageJob::class, fn (ImportImageJob $job): bool => $job->modelId === $failed->id);
    }
}
