<?php

declare(strict_types=1);

namespace App\Domain\Payment;

final class PaymentInitiation
{
    public function __construct(
        public string $provider,
        public ?string $reference = null,
        public ?string $redirectUrl = null,
    ) {}
}
