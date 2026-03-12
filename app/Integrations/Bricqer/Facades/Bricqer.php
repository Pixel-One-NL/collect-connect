<?php

namespace App\Integrations\Bricqer\Facades;

use App\Integrations\Bricqer\BricqerApi;
use Illuminate\Support\Facades\Facade;

class Bricqer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BricqerApi::class;
    }
}
