<?php

declare(strict_types=1);

use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/search', SearchController::class)
    ->middleware('throttle:api-search')
    ->name('api.search');
