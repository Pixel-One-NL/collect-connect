<?php

declare(strict_types=1);

namespace App\Domain\Payment\Drivers;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\PaymentInitiation;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Local/manual driver: records a pending payment without capturing funds.
 * Replace with MolliePaymentGateway (or similar) for production PSP flows.
 */
class ManualPaymentGateway implements PaymentGateway
{
    public function initiate(Order $order): PaymentInitiation
    {
        $isBankTransfer = $order->payment_method === 'bank';

        return new PaymentInitiation(
            provider: $isBankTransfer ? 'bank_transfer' : 'mollie_pending',
            reference: (string) Str::uuid(),
            redirectUrl: null,
        );
    }
}
