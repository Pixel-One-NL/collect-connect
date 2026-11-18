<?php

declare(strict_types=1);

namespace App\Http\Resources\Set;

use App\Models\Set;
use App\Support\ImageVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Set
 */
class SetSearchResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'set_num' => $this->set_num,
            'name' => $this->name,
            'year' => $this->year,
            'num_parts' => $this->num_parts,
            'image' => $this->imageUrl(ImageVariant::Thumb),
            'url' => route('sets.show', $this->resource, absolute: false),
        ];
    }
}
