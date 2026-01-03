<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartCategory extends Model
{
    /** @use HasFactory<\Database\Factories\PartCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'rebrickable_id',
        'name',
    ];
}
