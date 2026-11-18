<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Resources;

use App\Integrations\Cloudflare\DataTransferObjects\CloudflareVariant;
use App\Integrations\Cloudflare\Requests\Variants\CreateVariantRequest;
use App\Integrations\Cloudflare\Requests\Variants\ListVariantsRequest;
use App\Integrations\Cloudflare\Requests\Variants\UpdateVariantRequest;
use Illuminate\Support\Collection;

class VariantResource extends BaseResource
{
    /**
     * @return Collection<string, CloudflareVariant>
     */
    public function list(): Collection
    {
        return $this->connector->send(new ListVariantsRequest)->dtoOrFail();
    }

    /**
     * @param  array<string, int|string>  $options
     */
    public function create(string $variantId, array $options): void
    {
        $this->connector->send(new CreateVariantRequest($variantId, $options));
    }

    /**
     * @param  array<string, int|string>  $options
     */
    public function update(string $variantId, array $options): void
    {
        $this->connector->send(new UpdateVariantRequest($variantId, $options));
    }
}
