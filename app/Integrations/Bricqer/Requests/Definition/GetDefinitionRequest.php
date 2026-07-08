<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Requests\Definition;

use App\Integrations\Bricqer\DataTransferObjects\Definition\Definition;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetDefinitionRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $definitionId) {}

    public function resolveEndpoint(): string
    {
        return "/definitions/lego/definition/{$this->definitionId}";
    }

    public function createDtoFromResponse(Response $response): Definition
    {
        return Definition::from($response->json());
    }
}
