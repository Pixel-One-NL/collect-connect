<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Page;
use App\Models\Product;
use App\Models\Set;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('catalog.parts'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('catalog.minifigs'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('sets.index'), 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => url('/blog'), 'changefreq' => 'weekly', 'priority' => '0.6'],
        ]);

        Product::query()->select('id', 'updated_at')->orderBy('id')->chunk(500, function ($products) use ($urls): void {
            foreach ($products as $product) {
                $urls->push([
                    'loc' => route('product.show', $product),
                    'lastmod' => optional($product->updated_at)->toAtomString(),
                    'changefreq' => 'daily',
                    'priority' => '0.7',
                ]);
            }
        });

        Set::query()->select('id', 'updated_at')->orderBy('id')->chunk(500, function ($sets) use ($urls): void {
            foreach ($sets as $set) {
                $urls->push([
                    'loc' => route('sets.show', $set),
                    'lastmod' => optional($set->updated_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ]);
            }
        });

        Article::query()->where('is_published', true)->select('id', 'slug', 'updated_at')->each(function (Article $article) use ($urls): void {
            $urls->push([
                'loc' => url('/blog/'.$article->slug),
                'lastmod' => optional($article->updated_at)->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ]);
        });

        Page::query()->where('is_published', true)->select('id', 'slug', 'updated_at')->each(function (Page $page) use ($urls): void {
            $urls->push([
                'loc' => route('pages.show', $page),
                'lastmod' => optional($page->updated_at)->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ]);
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>'
                .'<loc>'.e($url['loc']).'</loc>'
                .(isset($url['lastmod']) && $url['lastmod'] ? '<lastmod>'.e($url['lastmod']).'</lastmod>' : '')
                .'<changefreq>'.e($url['changefreq']).'</changefreq>'
                .'<priority>'.e($url['priority']).'</priority>'
                .'</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
