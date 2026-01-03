<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Contracts;

interface RebrickableDownloader
{
    /**
     * @return array<array<string, mixed>>
     */
    public function retrieveRebrickableDataFromUrl(string $url): array;
}
