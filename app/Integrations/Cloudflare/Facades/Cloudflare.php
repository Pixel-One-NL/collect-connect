<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Facades;

use App\Integrations\Cloudflare\CloudflareApi;
use Illuminate\Support\Facades\Facade;

class Cloudflare extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CloudflareApi::class;
    }
}
