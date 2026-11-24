<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        return inertia('blog/index', [
            'articles' => ArticleResource::collection(Article::query()
                ->published()
                ->latest('published_at')
                ->latest('id')
                ->paginate(10)),
        ]);
    }

    public function show(Article $article): Response
    {
        abort_unless(Article::query()->published()->whereKey($article->id)->exists(), 404);

        return inertia('blog/article', [
            'article' => (new ArticleResource($article))->detailed(),
        ]);
    }
}
