<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Requests\Images;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteImageRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(protected string $imageId) {}

    public function resolveEndpoint(): string
    {
        return '/images/v1/'.rawurlencode($this->imageId);
    }
}
