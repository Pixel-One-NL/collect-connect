<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Resources\ArticleResource;
use App\Models\Concerns\HasCloudflareImage;
use App\Models\Contracts\CloudflareImageable;
use Carbon\CarbonInterface;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 */
#[UseResource(ArticleResource::class)]
class Article extends Model implements CloudflareImageable
{
    /** @use HasFactory<ArticleFactory> */
    use HasCloudflareImage, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'is_published',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where(fn (Builder $query) => $query
            ->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    protected function cloudflareImageType(): string
    {
        return 'article';
    }

    /**
     * Articles published without a date went live when they were created.
     */
    public function publicationDate(): ?CarbonInterface
    {
        return $this->published_at ?? $this->created_at;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
