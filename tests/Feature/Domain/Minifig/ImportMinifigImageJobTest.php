<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Minifig;

use App\Domain\Image\Jobs\ImportImageJob;
use App\Domain\Minifig\Jobs\ImportMinifigImageFromDefinitionJob;
use App\Domain\Part\Jobs\FullImportPartColorImagesJob;
use App\Http\Resources\Product\ProductResource;
use App\Integrations\Bricqer\Requests\Definition\GetDefinitionRequest;
use App\Integrations\Bricqer\Requests\Definition\ListDefinitionsRequest;
use App\Models\Color;
use App\Models\Minifig;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class ImportMinifigImageJobTest extends TestCase
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

    public function test_product_resource_returns_cloudflare_image_for_minifig(): void
    {
        $minifig = Minifig::factory()->create([
            'bricklink_id' => 'sw0001',
            'cloudflare_image_id' => 'testing-minifig-1',
        ]);

        $product = Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
        ]);

        $product->load(['productable', 'color']);

        $data = (new ProductResource($product))->toArray(Request::create('/'));

        $this->assertSame('https://imagedelivery.net/test-hash/testing-minifig-1/large', $data['image']);
    }

    public function test_product_resource_does_not_fall_back_to_bricqer_cdn_for_minifigs(): void
    {
        $minifig = Minifig::factory()->create(['bricklink_id' => 'sw0001']);
        $product = Product::factory()->create([
            'productable_type' => $minifig->getMorphClass(),
            'productable_id' => $minifig->id,
            'color_id' => Color::factory(),
        ]);

        $product->load(['productable', 'color']);

        $data = (new ProductResource($product))->toArray(Request::create('/'));

        $this->assertNull($data['image']);
    }

    public function test_full_import_queues_minifig_images_from_definitions(): void
    {
        Queue::fake();

        $minifig = Minifig::factory()->create([
            'bricklink_id' => 'sh0831',
        ]);

        Saloon::fake([
            ListDefinitionsRequest::class => MockResponse::make([
                'page' => [
                    'count' => 1,
                    'number' => 1,
                    'size' => 100,
                    'links' => ['next' => null, 'previous' => null],
                ],
                'results' => [
                    $this->definition(3, 'M', 'sh0831', 4.86, 'https://cdn.example.test/sh0831.png'),
                ],
            ]),
        ]);

        $stats = (new FullImportPartColorImagesJob)->handle();

        $this->assertSame(1, $stats['images_queued']);
        Queue::assertPushed(ImportImageJob::class, function (ImportImageJob $job) use ($minifig): bool {
            return $job->modelClass === Minifig::class
                && $job->modelId === $minifig->id
                && $job->imageUrl === 'https://cdn.example.test/sh0831.png';
        });
    }

    public function test_from_definition_job_sets_url_and_dispatches_download(): void
    {
        Queue::fake();

        $minifig = Minifig::factory()->create([
            'bricklink_id' => 'sw0001',
            'bricqer_definition_id' => '42',
        ]);

        // Match the live single-definition payload (no `id`, uses remainingQuantity).
        Saloon::fake([
            GetDefinitionRequest::class => MockResponse::make([
                'legoType' => 'M',
                'legoId' => 'sw0001',
                'legoIdFull' => 'Minifig sw0001',
                'picture' => 'https://cdn.example.test/sw0001.png',
                'legoCategoryId' => 1,
                'comment' => null,
                'eanNumber' => null,
                'completeness' => null,
                'weight' => 3.2,
                'description' => 'Test',
                'condition' => 'N',
                'color' => null,
                'price' => 0.1,
                'minPrice' => null,
                'priceOverrides' => [],
                'priceLocked' => false,
                'salePercent' => null,
                'bulkQty' => 1,
                'requiresComment' => false,
                'remainingQuantity' => null,
            ]),
        ]);

        (new ImportMinifigImageFromDefinitionJob($minifig->id))->handle();

        Queue::assertPushed(ImportImageJob::class, function (ImportImageJob $job) use ($minifig): bool {
            return $job->modelClass === Minifig::class
                && $job->modelId === $minifig->id
                && $job->imageUrl === 'https://cdn.example.test/sw0001.png';
        });
    }

    public function test_command_queues_eligible_minifigs_with_bricklink_id(): void
    {
        Queue::fake();

        $eligible = Minifig::factory()->create([
            'bricklink_id' => 'sw0001',
            'bricqer_definition_id' => null,
        ]);
        Minifig::factory()->withCloudflareImage()->create([
            'bricklink_id' => 'sw0002',
        ]);
        Minifig::factory()->create([
            'bricklink_id' => 'sw0003',
            'cloudflare_image_failed_at' => now(),
        ]);
        Minifig::factory()->create([
            'bricklink_id' => null,
            'bricqer_definition_id' => null,
        ]);

        $this->artisan('minifig:import-images')
            ->expectsOutputToContain('Queued 1 minifig image imports.')
            ->assertSuccessful();

        Queue::assertPushed(ImportMinifigImageFromDefinitionJob::class, function (ImportMinifigImageFromDefinitionJob $job) use ($eligible): bool {
            return $job->minifigId === $eligible->id;
        });
        Queue::assertPushed(ImportMinifigImageFromDefinitionJob::class, 1);
    }

    public function test_from_definition_job_falls_back_to_cdn_when_no_definition_id(): void
    {
        Queue::fake();

        $minifig = Minifig::factory()->create([
            'bricklink_id' => 'sw1085',
            'bricqer_definition_id' => null,
        ]);

        (new ImportMinifigImageFromDefinitionJob($minifig->id))->handle();

        Queue::assertPushed(ImportImageJob::class, function (ImportImageJob $job) use ($minifig): bool {
            return $job->modelClass === Minifig::class
                && $job->modelId === $minifig->id
                && $job->imageUrl === 'https://cdn.bricqer.com/static/bl-cache/MN/0/sw1085.png';
        });
    }

    public function test_command_respects_limit(): void
    {
        Queue::fake();

        Minifig::factory()->count(5)->create();

        $this->artisan('minifig:import-images', ['--limit' => 2])
            ->expectsOutputToContain('Queued 2 minifig image imports.')
            ->assertSuccessful();

        Queue::assertPushed(ImportMinifigImageFromDefinitionJob::class, 2);
    }

    public function test_command_includes_failed_minifigs_with_retry_failed_option(): void
    {
        Queue::fake();

        Minifig::factory()->create([
            'bricklink_id' => 'sw0003',
            'cloudflare_image_failed_at' => now(),
        ]);

        $this->artisan('minifig:import-images', ['--retry-failed' => true])
            ->expectsOutputToContain('Queued 1 minifig image imports.')
            ->assertSuccessful();
    }

    public function test_from_definition_job_skips_minifig_with_cloudflare_image(): void
    {
        Queue::fake();

        $minifig = Minifig::factory()->withCloudflareImage()->create(['bricklink_id' => 'sw1085']);

        (new ImportMinifigImageFromDefinitionJob($minifig->id))->handle();

        Queue::assertNotPushed(ImportImageJob::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(int $id, string $type, string $legoId, float $weight, ?string $picture = null): array
    {
        return [
            'id' => $id,
            'legoType' => $type,
            'legoId' => $legoId,
            'legoIdFull' => "{$type} {$legoId}",
            'picture' => $picture,
            'legoCategoryId' => 1,
            'comment' => null,
            'eanNumber' => null,
            'completeness' => null,
            'weight' => $weight,
            'description' => 'Test',
            'condition' => 'N',
            'color' => null,
            'price' => 0.1,
            'minPrice' => null,
            'priceOverrides' => [],
            'priceLocked' => false,
            'salePercent' => null,
            'bulkQty' => 1,
            'requiresComment' => false,
            'totalRemainingQuantity' => 1,
        ];
    }
}
