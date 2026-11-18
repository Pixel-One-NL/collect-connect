<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Requests\Definition;

use Illuminate\Support\Str;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Definition pictures are login protected paths that redirect to the public
 * image. Redirects are not followed, so only the location is read and the image
 * itself is never downloaded.
 */
class GetDefinitionPictureRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $picturePath) {}

    public function resolveEndpoint(): string
    {
        return Str::after($this->picturePath, '/api/v1');
    }

    /**
     * @return array{allow_redirects: false}
     */
    protected function defaultConfig(): array
    {
        return ['allow_redirects' => false];
    }
}
