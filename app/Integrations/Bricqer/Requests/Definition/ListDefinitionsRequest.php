<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Requests\Definition;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListDefinitionsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected int $page = 1) {}

    public function resolveEndpoint(): string
    {
        return '/definitions/lego/definition';
    }

    /**
     * @return array<string, int>
     */
    protected function defaultQuery(): array
    {
        return [
            'page' => $this->page,
        ];
    }
}
