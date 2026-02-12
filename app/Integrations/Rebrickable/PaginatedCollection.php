<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends Collection<TKey, TValue>
 */
class PaginatedCollection extends Collection
{
    protected int $currentPage;

    protected int $pageSize;

    protected int $totalCount;

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function setTotalCount(int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->totalCount / $this->pageSize);
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    public function isLastPage(): bool
    {
        return ! $this->hasNextPage();
    }
}
