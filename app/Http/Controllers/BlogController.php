<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;

class BlogController extends Controller
{
    public function index()
    {
        return inertia('blog/index', [
            'articles' => Article::query()
                ->where('is_published', true)
                ->latest('published_at')
                ->paginate(10),
        ]);
    }

    public function show(Article $article)
    {
        abort_unless($article->is_published, 404);

        return inertia('blog/article', [
            'article' => $article,
        ]);
    }
}
