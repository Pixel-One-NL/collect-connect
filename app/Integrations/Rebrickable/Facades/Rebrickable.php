<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Facades;

use App\Integrations\Rebrickable\RebrickableApi;
use Illuminate\Support\Facades\Facade;

class Rebrickable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RebrickableApi::class;
    }
}
