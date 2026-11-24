<?php

declare(strict_types=1);

namespace Tests\Feature\Content;

use App\Models\Article;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ContentPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_and_draft_content_is_hidden_everywhere(): void
    {
        foreach ([Page::class, Article::class] as $model) {
            $record = $model::query()->create(['title' => 'Future', 'slug' => 'future', 'content' => 'Hidden', 'is_published' => true, 'published_at' => now()->addDay()]);
            $this->get($model === Page::class ? route('pages.show', $record) : route('blog.show', $record))->assertNotFound();
        }
        $this->get(route('blog.index'))->assertInertia(fn ($page) => $page->has('articles.data', 0));
        foreach (['pages', 'articles'] as $type) {
            $this->get(route('sitemap.child', ['type' => $type, 'page' => 1]))->assertOk()->assertDontSee('future');
        }
    }

    public function test_rich_content_is_sanitized_without_changing_stored_content(): void
    {
        $article = Article::query()->create(['title' => 'Article', 'slug' => 'article', 'content' => '<p>Hello</p><script>alert(1)</script>', 'is_published' => true]);
        $this->get(route('blog.show', $article))->assertInertia(fn ($page) => $page->where('article.data.content', fn ($value) => str_contains($value, 'Hello') && ! str_contains($value, '<script>')));
        $this->assertStringContainsString('<script>', $article->fresh()->content);
    }

    public function test_blog_exposes_cover_image_date_and_excerpt(): void
    {
        config(['cloudflare.delivery_url' => 'https://imagedelivery.net', 'cloudflare.account_hash' => 'hash']);
        $withImage = Article::factory()->create(['slug' => 'with-image', 'is_published' => true, 'published_at' => now()->subDay(), 'content' => '<p>Een <strong>lang</strong> verhaal</p>', 'cloudflare_image_id' => 'testing-article-1-abc']);
        Article::factory()->create(['slug' => 'without-image', 'is_published' => true, 'published_at' => now()->subDays(2), 'meta_description' => 'Korte samenvatting']);

        $this->get(route('blog.index'))->assertInertia(fn ($page) => $page
            ->has('articles.data', 2)
            ->has('articles.meta.links')
            ->where('articles.data.0.slug', 'with-image')
            ->where('articles.data.0.image', 'https://imagedelivery.net/hash/testing-article-1-abc/medium')
            ->where('articles.data.0.excerpt', 'Een lang verhaal')
            ->where('articles.data.0.published_at', $withImage->published_at->toIso8601String())
            ->where('articles.data.0.url', route('blog.show', $withImage))
            ->missing('articles.data.0.content')
            ->where('articles.data.1.image', null)
            ->where('articles.data.1.excerpt', 'Korte samenvatting'));

        $this->get(route('blog.show', $withImage))->assertInertia(fn ($page) => $page
            ->where('article.data.image', 'https://imagedelivery.net/hash/testing-article-1-abc/large')
            ->where('article.data.published_at', $withImage->published_at->toIso8601String())
            ->where('article.data.content', fn ($value) => str_contains($value, '<strong>lang</strong>')));
    }

    public function test_sitemap_index_and_children_are_public_and_validate_types(): void
    {
        Cache::flush();
        $this->get(route('sitemap'))->assertOk()->assertSee('sitemapindex')->assertSee('/sitemaps/products/1.xml');
        $this->get(route('sitemap.child', ['type' => 'invalid', 'page' => 1]))->assertNotFound();
        $this->get(route('sitemap.child', ['type' => 'static', 'page' => 2]))->assertNotFound();
    }
}
