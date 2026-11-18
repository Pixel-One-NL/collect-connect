<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Requests\Images;

use App\Integrations\Cloudflare\DataTransferObjects\CloudflareImage;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasMultipartBody;

class UploadImageFromUrlRequest extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, string>  $metadata
     */
    public function __construct(
        protected string $imageId,
        protected string $sourceUrl,
        protected array $metadata = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/images/v1';
    }

    /**
     * @return array<int, MultipartValue>
     */
    protected function defaultBody(): array
    {
        return [
            new MultipartValue('id', $this->imageId),
            new MultipartValue('url', $this->sourceUrl),
            new MultipartValue('metadata', json_encode((object) $this->metadata, JSON_THROW_ON_ERROR)),
        ];
    }

    public function createDtoFromResponse(Response $response): CloudflareImage
    {
        return CloudflareImage::from($response->json('result'));
    }
}
