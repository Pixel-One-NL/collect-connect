<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ImportsRebrickableEntity
{
    public function getUrl(): string;

    /**
     * @return class-string<Model>
     */
    public function getModel(): string;

    public function import(): void;

    /**
     * @return class-string<MapsRebrickableEntity>
     */
    public function getMapper(): string;
}
