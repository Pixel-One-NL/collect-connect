<?php

declare(strict_types=1);

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\PaymentInitiation;
use App\Models\Order;

interface PaymentGateway
{
    /**
     * Start payment for a pending order. Does not mark the order paid.
     */
    public function initiate(Order $order): PaymentInitiation;
}
