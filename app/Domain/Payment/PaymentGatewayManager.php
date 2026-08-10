<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use App\Domain\Payment\Contracts\PaymentGateway;
use Illuminate\Support\Manager;
use InvalidArgumentException;

/**
 * Resolves the active payment gateway.
 *
 * Adding a provider is a two step job: implement PaymentGateway, then map a
 * driver name to that class in config/payment.php. No changes here are needed.
 */
class PaymentGatewayManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return (string) $this->config->get('payment.default');
    }

    /**
     * @return list<string>
     */
    public function availableDrivers(): array
    {
        return array_keys((array) $this->config->get('payment.drivers', []));
    }

    protected function createDriver($driver): PaymentGateway
    {
        if (isset($this->customCreators[$driver])) {
            $gateway = $this->callCustomCreator($driver);
        } else {
            $gateway = $this->container->make($this->driverClass($driver));
        }

        if (! $gateway instanceof PaymentGateway) {
            throw new InvalidArgumentException(
                "Payment driver [{$driver}] must implement ".PaymentGateway::class.'.'
            );
        }

        return $gateway;
    }

    /**
     * @return class-string
     */
    protected function driverClass(string $driver): string
    {
        $class = $this->config->get("payment.drivers.{$driver}");

        if (! is_string($class) || ! class_exists($class)) {
            throw new InvalidArgumentException(
                "Payment driver [{$driver}] is not configured in config/payment.php."
            );
        }

        return $class;
    }
}
