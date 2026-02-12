<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Requests;

use Saloon\Enums\Method;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;

abstract class PaginatedRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @var int<1, max>
     */
    protected int $page = 1;

    /**
     * @var int<1, 1000>
     */
    protected int $pageSize = 100;

    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param  int<1, max>  $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param  int<1, 1000>  $pageSize
     */
    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function boot(PendingRequest $pendingRequest): void
    {
        $pendingRequest->query()->add('page', $this->getPage());
        $pendingRequest->query()->add('page_size', $this->getPageSize());
    }
}
