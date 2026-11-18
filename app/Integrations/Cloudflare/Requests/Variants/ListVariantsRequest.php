<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Requests\Variants;

use App\Integrations\Cloudflare\DataTransferObjects\CloudflareVariant;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListVariantsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/images/v1/variants';
    }

    /**
     * @return Collection<string, CloudflareVariant>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        return $response->collect('result.variants')
            ->map(fn (array $variant): CloudflareVariant => CloudflareVariant::from($variant));
    }
}
