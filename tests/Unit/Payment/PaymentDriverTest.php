<?php

declare(strict_types=1);

namespace Tests\Unit\Payment;

use App\Domain\Payment\Drivers\ManualPaymentGateway;
use App\Domain\Payment\Drivers\TestingPaymentGateway;
use App\Domain\Payment\PaymentStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

class PaymentDriverTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_driver_marks_bank_transfers_separately(): void
    {
        $gateway = new ManualPaymentGateway;

        $bank = $gateway->initiate(Order::factory()->create(['payment_method' => 'bank']));
        $ideal = $gateway->initiate(Order::factory()->create(['payment_method' => 'ideal']));

        $this->assertSame('bank_transfer', $bank->provider);
        $this->assertSame('mollie_pending', $ideal->provider);
        $this->assertNotNull($bank->reference);
        $this->assertNull($bank->redirectUrl, 'The manual driver has no hosted payment page.');
    }

    public function test_manual_driver_never_confirms_payment_on_its_own(): void
    {
        $result = (new ManualPaymentGateway)->handleCallback(new Request);

        $this->assertSame(PaymentStatus::Pending, $result->status);
        $this->assertFalse($result->isPaid());
    }

    public function test_testing_driver_redirects_to_the_local_simulator(): void
    {
        $order = Order::factory()->create();

        $initiation = (new TestingPaymentGateway)->initiate($order);

        $this->assertSame('testing', $initiation->provider);
        $this->assertStringStartsWith('test_', (string) $initiation->reference);
        $this->assertSame(route('checkout.payment.simulate', $order), $initiation->redirectUrl);
    }

    public function test_testing_driver_reports_the_simulated_outcome(): void
    {
        $gateway = new TestingPaymentGateway;

        $this->assertTrue($gateway->handleCallback(new Request(['outcome' => 'paid']))->isPaid());
        $this->assertSame(
            PaymentStatus::Failed,
            $gateway->handleCallback(new Request(['outcome' => 'failed']))->status,
        );
    }

    public function test_testing_driver_refuses_to_boot_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must never be active in production');

        new TestingPaymentGateway;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function environmentDefaults(): array
    {
        return [
            ['local', 'testing'],
            ['staging', 'testing'],
            ['production', 'manual'],
            ['testing', 'manual'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('environmentDefaults')]
    public function test_default_driver_is_derived_from_the_environment(string $env, string $expected): void
    {
        $this->assertSame($expected, $this->resolveConfiguredDefault($env, null));
    }

    public function test_an_explicit_payment_driver_overrides_the_environment_default(): void
    {
        $this->assertSame('manual', $this->resolveConfiguredDefault('staging', 'manual'));
        $this->assertSame('testing', $this->resolveConfiguredDefault('production', 'testing'));
    }

    /**
     * Re-evaluates config/payment.php under a given environment, since the
     * default driver is resolved from env() at config load time.
     */
    private function resolveConfiguredDefault(string $appEnv, ?string $paymentDriver): string
    {
        $originalEnv = [$_ENV, $_SERVER];

        $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = $appEnv;

        if ($paymentDriver === null) {
            unset($_ENV['PAYMENT_DRIVER'], $_SERVER['PAYMENT_DRIVER']);
        } else {
            $_ENV['PAYMENT_DRIVER'] = $_SERVER['PAYMENT_DRIVER'] = $paymentDriver;
        }

        try {
            /** @var array{default: string} $config */
            $config = require base_path('config/payment.php');

            return $config['default'];
        } finally {
            [$_ENV, $_SERVER] = $originalEnv;
        }
    }
}
