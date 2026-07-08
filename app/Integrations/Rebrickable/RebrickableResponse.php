<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable;

use App\Integrations\Rebrickable\Requests\PaginatedRequest;
use LogicException;
use Saloon\Http\Response;

class RebrickableResponse extends Response
{
    public function dto(): mixed
    {
        $dataObject = parent::dto();
        $request = $this->getRequest();

        if (! $request instanceof PaginatedRequest) {
            return $dataObject;
        }

        if (! $dataObject instanceof PaginatedCollection) {
            throw new LogicException('Expected PaginatedCollection for paginated request');
        }

        $dataObject->setPageSize($request->getPageSize());
        $dataObject->setCurrentPage($request->getPage());
        $dataObject->setTotalCount($this->json('count'));

        return $dataObject;
    }
}
