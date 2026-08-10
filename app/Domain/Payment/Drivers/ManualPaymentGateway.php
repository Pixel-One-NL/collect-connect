<?php

declare(strict_types=1);

namespace App\Domain\Payment\Drivers;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\PaymentInitiation;
use App\Domain\Payment\PaymentResult;
use App\Domain\Payment\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Manual driver: records a pending payment without capturing funds, for
 * bank-transfer style reconciliation. Real PSP drivers (Mollie, Stripe, ...)
 * implement the same contract and are registered in config/payment.php.
 */
class ManualPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'manual';
    }

    public function initiate(Order $order): PaymentInitiation
    {
        $isBankTransfer = $order->payment_method === 'bank';

        return new PaymentInitiation(
            provider: $isBankTransfer ? 'bank_transfer' : 'mollie_pending',
            reference: (string) Str::uuid(),
            redirectUrl: null,
        );
    }

    /**
     * No provider calls back into a manual flow; payments are reconciled by hand.
     */
    public function handleCallback(Request $request): PaymentResult
    {
        return new PaymentResult(
            provider: $this->name(),
            status: PaymentStatus::Pending,
            reference: $request->string('reference')->toString() ?: null,
            payload: $request->all(),
        );
    }
}
