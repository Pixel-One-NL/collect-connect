<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations\Cloudflare;

use App\Integrations\Cloudflare\Exceptions\ImageAlreadyExistsException;
use App\Integrations\Cloudflare\Exceptions\ImageSourceUnreachableException;
use App\Integrations\Cloudflare\Facades\Cloudflare;
use App\Integrations\Cloudflare\Requests\Images\DeleteImageRequest;
use App\Integrations\Cloudflare\Requests\Images\GetImageRequest;
use App\Integrations\Cloudflare\Requests\Images\UploadImageFromUrlRequest;
use Saloon\Exceptions\Request\Statuses\ForbiddenException;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class CloudflareImagesApiTest extends TestCase
{
    public function test_upload_sends_url_id_and_metadata_as_multipart_with_bearer_token(): void
    {
        $mockClient = Saloon::fake([
            UploadImageFromUrlRequest::class => MockResponse::make([
                'success' => true,
                'result' => ['id' => 'testing-set-1', 'metadata' => ['source_url' => 'https://example.test/a.png']],
            ]),
        ]);

        $image = Cloudflare::images()->uploadFromUrl('testing-set-1', 'https://example.test/a.png', ['source_url' => 'https://example.test/a.png']);

        $this->assertSame('testing-set-1', $image->id);
        $this->assertSame('https://example.test/a.png', $image->metadata['source_url']);

        $mockClient->assertSent(function (UploadImageFromUrlRequest $request, $response): bool {
            $pendingRequest = $response->getPendingRequest();
            $body = $pendingRequest->body();

            return $pendingRequest->getUrl() === 'https://api.cloudflare.com/client/v4/accounts/test-account/images/v1'
                && $pendingRequest->headers()->get('Authorization') === 'Bearer test-token'
                && str_starts_with((string) $pendingRequest->headers()->get('Content-Type'), 'multipart/form-data')
                && $body->get('id')->value === 'testing-set-1'
                && $body->get('url')->value === 'https://example.test/a.png'
                && $body->get('metadata')->value === '{"source_url":"https:\/\/example.test\/a.png"}';
        });
    }

    public function test_upload_translates_configured_error_codes(): void
    {
        config([
            'cloudflare.error_codes.image_already_exists' => [1001],
            'cloudflare.error_codes.source_unreachable' => [1002],
        ]);

        Saloon::fake([
            MockResponse::make(['success' => false, 'errors' => [['code' => 1001, 'message' => 'exists']]], 409),
            MockResponse::make(['success' => false, 'errors' => [['code' => 1002, 'message' => 'cannot fetch']]], 400),
        ]);

        try {
            Cloudflare::images()->uploadFromUrl('testing-set-1', 'https://example.test/a.png');
            $this->fail('Expected ImageAlreadyExistsException.');
        } catch (ImageAlreadyExistsException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(ImageSourceUnreachableException::class);
        Cloudflare::images()->uploadFromUrl('testing-set-1', 'https://example.test/a.png');
    }

    public function test_upload_rethrows_unknown_errors(): void
    {
        config(['cloudflare.error_codes.source_unreachable' => [1002]]);

        Saloon::fake([
            MockResponse::make(['success' => false, 'errors' => [['code' => 10000, 'message' => 'Authentication error']]], 403),
        ]);

        $this->expectException(ForbiddenException::class);

        Cloudflare::images()->uploadFromUrl('testing-set-1', 'https://example.test/a.png');
    }

    public function test_get_and_delete_use_image_id_in_url(): void
    {
        $mockClient = Saloon::fake([
            GetImageRequest::class => MockResponse::make(['result' => ['id' => 'testing-set-1']]),
            DeleteImageRequest::class => MockResponse::make(['success' => true]),
        ]);

        $this->assertSame([], Cloudflare::images()->get('testing-set-1')->metadata);
        Cloudflare::images()->delete('testing-set-1');

        $mockClient->assertSent(fn (DeleteImageRequest $request): bool => $request->resolveEndpoint() === '/images/v1/testing-set-1');
    }
}
