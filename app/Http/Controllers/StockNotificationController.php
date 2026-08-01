<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Shop\StoreStockNotificationRequest;
use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Http\RedirectResponse;

class StockNotificationController extends Controller
{
    public function store(StoreStockNotificationRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        if ($product->stock > 0) {
            return back()->with('status', 'Dit product is al op voorraad.');
        }

        StockNotification::query()->firstOrCreate([
            'product_id' => $product->id,
            'email' => strtolower($validated['email']),
        ]);

        return back()->with('status', 'We mailen je zodra dit product weer op voorraad is.');
    }
}
