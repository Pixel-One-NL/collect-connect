<?php

declare(strict_types=1);

namespace App\Http\Resources\Set;

use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Set
 */
class SetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'set_num' => $this->set_num,
            'name' => $this->name,
            'year' => $this->year,
            'num_parts' => $this->num_parts,
            'image' => $this->getFirstMediaUrl(Set::IMAGE_COLLECTION, Set::LARGE_CONVERSION) ?: null,
        ];
    }
}
