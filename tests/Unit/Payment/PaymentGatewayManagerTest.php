<?php

declare(strict_types=1);

namespace Tests\Unit\Payment;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Drivers\ManualPaymentGateway;
use App\Domain\Payment\Drivers\TestingPaymentGateway;
use App\Domain\Payment\PaymentGatewayManager;
use App\Domain\Payment\PaymentInitiation;
use App\Domain\Payment\PaymentResult;
use App\Domain\Payment\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use InvalidArgumentException;
use stdClass;
use Tests\TestCase;

class PaymentGatewayManagerTest extends TestCase
{
    private function manager(): PaymentGatewayManager
    {
        return $this->app->make(PaymentGatewayManager::class);
    }

    public function test_it_resolves_the_configured_default_driver(): void
    {
        config(['payment.default' => 'manual']);

        $this->assertInstanceOf(ManualPaymentGateway::class, $this->manager()->driver());
    }

    public function test_changing_the_configured_default_changes_the_resolved_driver(): void
    {
        config(['payment.default' => 'testing']);

        $this->assertInstanceOf(TestingPaymentGateway::class, $this->manager()->driver());
    }

    public function test_a_driver_can_be_resolved_by_name(): void
    {
        $this->assertInstanceOf(TestingPaymentGateway::class, $this->manager()->driver('testing'));
        $this->assertInstanceOf(ManualPaymentGateway::class, $this->manager()->driver('manual'));
    }

    public function test_adding_a_provider_only_requires_a_config_entry(): void
    {
        config([
            'payment.drivers.acme' => AcmePaymentGateway::class,
            'payment.default' => 'acme',
        ]);

        $gateway = $this->manager()->driver();

        $this->assertInstanceOf(AcmePaymentGateway::class, $gateway);
        $this->assertSame('acme', $gateway->name());
    }

    public function test_it_rejects_a_driver_that_is_not_configured(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment driver [nope] is not configured');

        $this->manager()->driver('nope');
    }

    public function test_it_rejects_a_driver_that_does_not_implement_the_contract(): void
    {
        config(['payment.drivers.bogus' => stdClass::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        $this->manager()->driver('bogus');
    }

    public function test_the_container_resolves_the_contract_through_the_manager(): void
    {
        config(['payment.default' => 'testing']);

        $this->assertInstanceOf(TestingPaymentGateway::class, $this->app->make(PaymentGateway::class));
    }

    public function test_available_drivers_are_read_from_config(): void
    {
        $this->assertSame(['manual', 'testing'], $this->manager()->availableDrivers());
    }
}

/**
 * Stands in for a future provider, proving a new gateway needs no change to
 * PaymentGatewayManager itself.
 */
class AcmePaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'acme';
    }

    public function initiate(Order $order): PaymentInitiation
    {
        return new PaymentInitiation(provider: 'acme', reference: 'acme_1', redirectUrl: 'https://acme.test/pay/1');
    }

    public function handleCallback(Request $request): PaymentResult
    {
        return new PaymentResult(provider: 'acme', status: PaymentStatus::Paid, reference: 'acme_1');
    }
}
