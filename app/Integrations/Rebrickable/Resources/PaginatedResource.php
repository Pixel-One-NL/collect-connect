<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Resources;

use App\Integrations\Rebrickable\PaginatedCollection;
use App\Integrations\Rebrickable\Requests\PaginatedRequest;

/**
 * @template T
 */
abstract class PaginatedResource extends BaseResource
{
    /**
     * @var int<1, max>
     */
    protected int $page = 1;

    /**
     * @var int<1, 1000>
     */
    protected int $pageSize = 10;

    /**
     * @param  int<1, max>  $page
     */
    public function setPage(int $page): static
    {
        $this->page = max(1, $page);

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPageSize(int $pageSize): static
    {
        $this->pageSize = min(1000, max(1, $pageSize));

        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    abstract public function getRequest(): PaginatedRequest;

    /**
     * @return PaginatedCollection<int, T>
     */
    public function get(): PaginatedCollection
    {
        $request = $this->getRequest();
        $request->setPageSize($this->pageSize);
        $request->setPage($this->page);

        return $this->connector->send($request)->dto();
    }
}
