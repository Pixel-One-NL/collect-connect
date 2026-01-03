<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minifig extends Model
{
    /** @use HasFactory<\Database\Factories\MinifigFactory> */
    use HasFactory;

    protected $fillable = [
        'rebrickable_id',
        'name',
    ];
}
