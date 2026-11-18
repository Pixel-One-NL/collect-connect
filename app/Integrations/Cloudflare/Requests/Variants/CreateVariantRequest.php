<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Requests\Variants;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateVariantRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, int|string>  $options
     */
    public function __construct(
        protected string $variantId,
        protected array $options,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/images/v1/variants';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'id' => $this->variantId,
            'options' => $this->options,
            'neverRequireSignedURLs' => true,
        ];
    }
}
