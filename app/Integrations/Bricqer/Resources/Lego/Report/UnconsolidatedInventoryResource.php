<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Resources\Lego\Report;

use App\Integrations\Bricqer\DataTransferObjects\UnconsolidatedInventory\InventoryItem;
use App\Integrations\Bricqer\Requests\Lego\Report\GetUnconsolidatedInventoryRequest;
use App\Integrations\Bricqer\Resources\BaseResource;
use Illuminate\Support\LazyCollection;

class UnconsolidatedInventoryResource extends BaseResource
{
    /**
     * @return LazyCollection<int, InventoryItem>
     */
    public function get(): LazyCollection
    {
        return $this->connector->send(new GetUnconsolidatedInventoryRequest)->dtoOrFail();
    }
}
