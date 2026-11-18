<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\DataTransferObjects;

use Spatie\LaravelData\Data;

class CloudflareImage extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $id,
        public array $metadata = [],
    ) {}
}
