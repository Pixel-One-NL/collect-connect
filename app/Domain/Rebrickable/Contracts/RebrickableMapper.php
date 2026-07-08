<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Contracts;

interface RebrickableMapper
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function map(array $row): array;

    /**
     * @return string|list<string>
     */
    public function getUniqueKey(): string|array;

    /**
     * @return array<string, string>
     */
    public function getMapping(): array;
}
