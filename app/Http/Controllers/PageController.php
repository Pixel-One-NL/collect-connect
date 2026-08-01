<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Response;

class PageController extends Controller
{
    public function show(Page $page): Response
    {
        abort_unless($page->is_published, 404);

        return inertia('pages/show', [
            'page' => $page,
        ]);
    }
}
