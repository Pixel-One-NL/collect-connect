<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CartService;
use Inertia\Response;

class CartPageController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function __invoke(): Response
    {
        $messages = $this->cartService->revalidateStock();
        $items = $this->cartService->getItems();

        return inertia('cart/index', [
            'items' => $items,
            'total' => $items->sum(fn (array $i): float => $i['price'] * $i['quantity']),
            'messages' => $messages,
        ]);
    }
}
