<?php

declare(strict_types=1);

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\PaymentInitiation;
use App\Domain\Payment\PaymentResult;
use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Identifier this gateway is registered under in config/payment.php.
     */
    public function name(): string;

    /**
     * Start payment for a pending order. Does not mark the order paid.
     */
    public function initiate(Order $order): PaymentInitiation;

    /**
     * Translate a provider callback/webhook into a payment outcome.
     * Does not mutate the order; the caller decides what to do with the result.
     */
    public function handleCallback(Request $request): PaymentResult;
}
