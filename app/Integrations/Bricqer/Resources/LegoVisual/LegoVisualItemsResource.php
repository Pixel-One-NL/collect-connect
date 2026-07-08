<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Resources\LegoVisual;

use App\Integrations\Bricqer\DataTransferObjects\LegoVisual\LegoVisualPage;
use App\Integrations\Bricqer\Requests\LegoVisual\ListItemsRequest;
use App\Integrations\Bricqer\Resources\BaseResource;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Http\Response;
use Throwable;

class LegoVisualItemsResource extends BaseResource
{
    protected ?string $partType = null;

    /**
     * Restrict the listing to a Bricqer part type (e.g. `P` for parts).
     */
    public function partType(string $type): self
    {
        $this->partType = $type;

        return $this;
    }

    public function get(int $page = 1): LegoVisualPage
    {
        return $this->connector
            ->send(new ListItemsRequest($this->partType, $page))
            ->dtoOrFail();
    }

    /**
     * Fetch several listing pages concurrently through a Saloon request pool.
     *
     * A page number past the end of the listing is not an error: Bricqer
     * answers it with a 404, which is simply omitted from the result so the
     * caller can detect the end of the walk. Any other failure is rethrown
     * after the pool has drained.
     *
     * @param  list<int>  $pageNumbers
     * @return array<int, LegoVisualPage> page number => page (404'd pages omitted)
     */
    public function getMany(array $pageNumbers, int $concurrency = 5): array
    {
        /** @var array<int, LegoVisualPage> $pages */
        $pages = [];

        /** @var Throwable|null $failure */
        $failure = null;

        $requests = collect($pageNumbers)
            ->mapWithKeys(fn (int $page): array => [$page => new ListItemsRequest($this->partType, $page)])
            ->all();

        $this->connector
            ->pool(
                requests: $requests,
                concurrency: $concurrency,
                responseHandler: function (Response $response, int|string $key) use (&$pages): void {
                    $pages[(int) $key] = $response->dtoOrFail();
                },
                exceptionHandler: function (Throwable $exception) use (&$failure): void {
                    if ($exception instanceof NotFoundException) {
                        return;
                    }

                    $failure ??= $exception;
                },
            )
            ->send()
            ->wait();

        if ($failure !== null) {
            throw $failure;
        }

        ksort($pages);

        return $pages;
    }
}
