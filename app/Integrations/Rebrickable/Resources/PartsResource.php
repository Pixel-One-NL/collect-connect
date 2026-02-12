<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Resources;

use App\Integrations\Rebrickable\Resources\Parts\ListPartsResource;

class PartsResource extends BaseResource
{
    public function list(): ListPartsResource
    {
        return new ListPartsResource($this->connector);
    }
}
