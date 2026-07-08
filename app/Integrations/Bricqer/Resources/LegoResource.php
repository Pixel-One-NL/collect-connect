<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Resources;

use App\Integrations\Bricqer\Resources\Lego\ReportResource;

class LegoResource extends BaseResource
{
    public function report(): ReportResource
    {
        return new ReportResource($this->connector);
    }
}
