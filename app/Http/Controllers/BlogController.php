<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;

class BlogController extends Controller
{
    public function index()
    {
        return inertia('blog/index', [
            'articles' => Article::paginate(10),
        ]);
    }

    public function show(Article $article)
    {
        return inertia('blog/article', [
            'article' => $article,
        ]);
    }
}
