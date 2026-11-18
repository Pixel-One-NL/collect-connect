<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Image;

use App\Integrations\Cloudflare\Requests\Variants\CreateVariantRequest;
use App\Integrations\Cloudflare\Requests\Variants\ListVariantsRequest;
use App\Integrations\Cloudflare\Requests\Variants\UpdateVariantRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class SyncCloudflareVariantsCommandTest extends TestCase
{
    public function test_config_keeps_the_former_conversion_sizes(): void
    {
        $this->assertSame([
            'thumb' => ['width' => 200, 'height' => 200, 'fit' => 'contain'],
            'medium' => ['width' => 400, 'height' => 400, 'fit' => 'contain'],
            'large' => ['width' => 800, 'height' => 800, 'fit' => 'contain'],
        ], config('cloudflare.variants'));
    }

    public function test_it_creates_missing_variants(): void
    {
        $mockClient = Saloon::fake([
            ListVariantsRequest::class => MockResponse::make(['result' => ['variants' => []]]),
            CreateVariantRequest::class => MockResponse::make(['success' => true]),
        ]);

        $this->artisan('cloudflare:sync-variants')->assertSuccessful();

        $mockClient->assertSentCount(3, CreateVariantRequest::class);
        $mockClient->assertSent(fn ($request): bool => $request instanceof CreateVariantRequest && $request->body()->all() === [
            'id' => 'thumb',
            'options' => ['width' => 200, 'height' => 200, 'fit' => 'contain', 'metadata' => 'none'],
            'neverRequireSignedURLs' => true,
        ]);
    }

    public function test_it_updates_changed_variants_and_leaves_matching_ones_alone(): void
    {
        $mockClient = Saloon::fake([
            ListVariantsRequest::class => MockResponse::make(['result' => ['variants' => [
                'thumb' => $this->variant('thumb', 200),
                'medium' => $this->variant('medium', 999),
                'large' => $this->variant('large', 800),
                'public' => $this->variant('public', 1366),
            ]]]),
            UpdateVariantRequest::class => MockResponse::make(['success' => true]),
        ]);

        $this->artisan('cloudflare:sync-variants')->assertSuccessful();

        $mockClient->assertSentCount(1, UpdateVariantRequest::class);
        $mockClient->assertNotSent(CreateVariantRequest::class);
        $mockClient->assertSent(fn ($request): bool => $request instanceof UpdateVariantRequest
            && $request->resolveEndpoint() === '/images/v1/variants/medium'
            && $request->body()->get('options')['width'] === 400);
    }

    /**
     * @return array<string, mixed>
     */
    private function variant(string $id, int $size): array
    {
        return [
            'id' => $id,
            'options' => ['fit' => 'contain', 'metadata' => 'none', 'width' => $size, 'height' => $size],
            'neverRequireSignedURLs' => true,
        ];
    }
}
