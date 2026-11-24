<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Article;
use App\Support\ImageVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Article */
class ArticleResource extends JsonResource
{
    private ImageVariant $imageVariant = ImageVariant::Medium;

    private bool $withContent = false;

    /**
     * Full detail for the article page: sanitized body and the large cover.
     */
    public function detailed(): static
    {
        $this->imageVariant = ImageVariant::Large;
        $this->withContent = true;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'url' => route('blog.show', $this->resource),
            'image' => $this->imageUrl($this->imageVariant),
            'excerpt' => $this->excerpt(),
            'published_at' => $this->publicationDate()?->toIso8601String(),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'content' => $this->when($this->withContent, fn (): string => Str::sanitizeHtml($this->content ?? '')),
        ];
    }

    private function excerpt(): string
    {
        if (filled($this->meta_description)) {
            return $this->meta_description;
        }

        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace('<', ' <', $this->content ?? '')), ENT_QUOTES | ENT_HTML5)));

        return Str::limit($text, 180);
    }
}
