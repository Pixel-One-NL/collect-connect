<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\DataTransferObjects;

use Spatie\LaravelData\Data;

class CloudflareVariant extends Data
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $id,
        public array $options,
        public bool $neverRequireSignedURLs = false,
    ) {}
}
