<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Resources\ArticleResource;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Attributes\UseResourceCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseResourceCollection(ArticleResource::class)]
#[UseResource(ArticleResource::class)]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
