<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $order->user_id !== null && $order->user_id === $user->id;
    }

    /**
     * Access to an order just placed in this session, for guests who have no
     * account to authenticate against.
     */
    public function viewPlaced(?User $user, Order $order): bool
    {
        if ($user !== null && $this->view($user, $order)) {
            return true;
        }

        return in_array($order->id, (array) session('checkout.placed_order_ids', []), true);
    }
}
