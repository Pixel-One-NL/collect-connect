<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare;

use App\Integrations\Cloudflare\Resources\ImageResource;
use App\Integrations\Cloudflare\Resources\VariantResource;

class CloudflareApi
{
    public function __construct(protected CloudflareConnector $connector) {}

    public function images(): ImageResource
    {
        return new ImageResource($this->connector);
    }

    public function variants(): VariantResource
    {
        return new VariantResource($this->connector);
    }
}
