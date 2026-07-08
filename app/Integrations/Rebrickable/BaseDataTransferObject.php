<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
abstract class BaseDataTransferObject extends Data {}
