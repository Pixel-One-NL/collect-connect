<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Contracts;

use Generator;

interface RebrickableDownloader
{
    /**
     * @return Generator<array<string, mixed>>
     */
    public function retrieveRebrickableDataFromUrl(string $url): Generator;
}
