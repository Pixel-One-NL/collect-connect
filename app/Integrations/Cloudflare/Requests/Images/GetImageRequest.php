<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Requests\Images;

use App\Integrations\Cloudflare\DataTransferObjects\CloudflareImage;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetImageRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $imageId) {}

    public function resolveEndpoint(): string
    {
        return '/images/v1/'.rawurlencode($this->imageId);
    }

    public function createDtoFromResponse(Response $response): CloudflareImage
    {
        return CloudflareImage::from($response->json('result'));
    }
}
