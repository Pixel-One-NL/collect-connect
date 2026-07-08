<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Bricqer;

use App\Domain\Bricqer\Jobs\AttachBricqerImagesJob;
use App\Models\Color;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachBricqerImagesJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Http::preventStrayRequests();
    }

    /**
     * A valid PNG (of the given square size) so Spatie can generate
     * conversions. Distinct sizes produce distinct bytes.
     */
    private function pngBytes(int $size = 1): string
    {
        $image = imagecreatetruecolor($size, $size);
        ob_start();
        imagepng($image);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    private function partColor(Part $part, ?Color $color = null): PartColor
    {
        return PartColor::create([
            'part_id' => $part->id,
            'color_id' => ($color ?? Color::factory()->create())->id,
        ]);
    }

    public function test_it_attaches_the_image_and_generates_conversions(): void
    {
        $url = 'https://cdn.bricqer.com/static/bl-cache/PN/0/3001.png';

        $part = Part::factory()->create(['bricqer_img_url' => $url]);
        $pivot = $this->partColor($part);

        $bytes = $this->pngBytes(8);
        Http::fake([$url => Http::response($bytes)]);

        (new AttachBricqerImagesJob([$part->id]))->handle();

        $media = $pivot->refresh()->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION);
        $this->assertNotNull($media);
        $this->assertSame($bytes, Storage::disk('public')->get($media->getPathRelativeToRoot()));

        // All three sizes are generated (the queue is sync in tests).
        foreach ([PartColor::THUMB_CONVERSION, PartColor::MEDIUM_CONVERSION, PartColor::LARGE_CONVERSION] as $conversion) {
            $this->assertTrue($media->hasGeneratedConversion($conversion), "Missing conversion: {$conversion}");
        }

        $this->assertStringEndsWith('.webp', $media->getPath(PartColor::THUMB_CONVERSION));
    }

    public function test_it_downloads_once_per_part_and_attaches_to_every_color(): void
    {
        $url = 'https://cdn.bricqer.com/img/shared.png';

        $part = Part::factory()->create(['bricqer_img_url' => $url]);
        $pivots = collect(range(1, 3))->map(fn (): PartColor => $this->partColor($part));

        Http::fake([$url => Http::response($this->pngBytes(8))]);

        (new AttachBricqerImagesJob([$part->id]))->handle();

        foreach ($pivots as $pivot) {
            $this->assertNotNull($pivot->refresh()->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION));
        }

        // One catalog image per part, no matter how many colors it is sold in.
        Http::assertSentCount(1);
    }

    public function test_it_is_idempotent_on_rerun(): void
    {
        $url = 'https://cdn.bricqer.com/img/3001.png';

        $part = Part::factory()->create(['bricqer_img_url' => $url]);
        $pivot = $this->partColor($part);

        Http::fake([$url => Http::response($this->pngBytes(8))]);

        (new AttachBricqerImagesJob([$part->id]))->handle();
        (new AttachBricqerImagesJob([$part->id]))->handle();

        $this->assertCount(1, $pivot->refresh()->getMedia(PartColor::BRICQER_IMAGE_COLLECTION));
        Http::assertSentCount(1);
    }

    public function test_it_skips_parts_without_a_bricqer_img_url(): void
    {
        $part = Part::factory()->create(['bricqer_img_url' => null]);
        $pivot = $this->partColor($part);

        Http::fake();

        (new AttachBricqerImagesJob([$part->id]))->handle();

        $this->assertNull($pivot->refresh()->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION));
        Http::assertNothingSent();
    }

    public function test_a_failed_download_leaves_the_part_color_as_a_candidate(): void
    {
        $url = 'https://cdn.bricqer.com/img/missing.png';

        $part = Part::factory()->create(['bricqer_img_url' => $url]);
        $pivot = $this->partColor($part);

        Http::fake([$url => Http::response('', 404)]);

        (new AttachBricqerImagesJob([$part->id]))->handle();

        $this->assertNull($pivot->refresh()->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION));
    }

    public function test_it_is_resilient_to_a_connection_error(): void
    {
        $part = Part::factory()->create(['bricqer_img_url' => 'https://cdn.bricqer.com/img/x.png']);
        $pivot = $this->partColor($part);

        Http::fake(fn (Request $request) => throw new ConnectionException('boom'));

        // A transport error for one url must not bubble out of the slice job.
        (new AttachBricqerImagesJob([$part->id]))->handle();

        $this->assertNull($pivot->refresh()->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION));
    }

    public function test_fan_out_batches_slices_of_twenty_five_parts(): void
    {
        Bus::fake();

        $color = Color::factory()->create();

        // 26 candidate parts -> 2 jobs of 25 and 1.
        for ($i = 0; $i < 26; $i++) {
            $part = Part::factory()->create(['bricqer_img_url' => "https://cdn.bricqer.com/img/{$i}.png"]);
            $this->partColor($part, $color);
        }

        $dispatched = AttachBricqerImagesJob::dispatchFanOut();

        $this->assertSame(2, $dispatched);

        Bus::assertBatched(function (PendingBatch $batch): bool {
            $sizes = collect($batch->jobs)
                ->map(fn (AttachBricqerImagesJob $job): int => count($job->partIds))
                ->sort()
                ->values()
                ->all();

            return $sizes === [1, 25];
        });
    }

    public function test_fan_out_dispatches_nothing_when_no_images_are_missing(): void
    {
        Bus::fake();

        $part = Part::factory()->create(['bricqer_img_url' => 'https://cdn.bricqer.com/img/x.png']);
        $pivot = $this->partColor($part);

        $pivot->addMediaFromString($this->pngBytes())
            ->usingFileName('existing.png')
            ->toMediaCollection(PartColor::BRICQER_IMAGE_COLLECTION);

        $this->assertSame(0, AttachBricqerImagesJob::dispatchFanOut());

        Bus::assertNothingBatched();
    }
}
