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
use RuntimeException;

/**
 * Fake PSP for local and staging: redirects to an in-app page where the payment
 * can be completed or failed by hand, exercising the same redirect + callback
 * flow a real provider uses. Never captures funds.
 *
 * Guarded three ways: config/payment.php only defaults to it outside production,
 * the constructor below refuses to boot in production, and the simulator routes
 * are not registered in production at all.
 */
class TestingPaymentGateway implements PaymentGateway
{
    public function __construct()
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'TestingPaymentGateway must never be active in production. Set PAYMENT_DRIVER to a real gateway.'
            );
        }
    }

    public function name(): string
    {
        return 'testing';
    }

    public function initiate(Order $order): PaymentInitiation
    {
        return new PaymentInitiation(
            provider: $this->name(),
            reference: 'test_'.Str::lower((string) Str::ulid()),
            redirectUrl: route('checkout.payment.simulate', $order),
        );
    }

    public function handleCallback(Request $request): PaymentResult
    {
        $succeeded = $request->input('outcome', 'paid') === 'paid';

        return new PaymentResult(
            provider: $this->name(),
            status: $succeeded ? PaymentStatus::Paid : PaymentStatus::Failed,
            reference: $request->string('reference')->toString() ?: null,
            payload: ['simulated' => true, 'outcome' => $succeeded ? 'paid' : 'failed'],
        );
    }
}
