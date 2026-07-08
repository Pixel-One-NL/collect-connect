<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Resources;

use App\Integrations\Bricqer\Resources\LegoVisual\LegoVisualItemsResource;

class LegoVisualResource extends BaseResource
{
    public function items(): LegoVisualItemsResource
    {
        return new LegoVisualItemsResource($this->connector);
    }
}
