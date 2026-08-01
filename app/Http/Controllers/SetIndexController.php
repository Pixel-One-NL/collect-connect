<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Set\SetResource;
use App\Models\Set;
use Illuminate\Http\Request;
use Inertia\Response;

class SetIndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));

        $sets = Set::query()
            ->with('media')
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('set_num', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('year')
            ->paginate(36)
            ->withQueryString();

        return inertia('sets/index', [
            'sets' => SetResource::collection($sets),
            'query' => $q,
        ]);
    }
}
