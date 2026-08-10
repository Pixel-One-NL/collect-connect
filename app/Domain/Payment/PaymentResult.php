<?php

declare(strict_types=1);

namespace App\Domain\Payment;

final class PaymentResult
{
    /**
     * @param  array<string, mixed>  $payload  Raw provider payload, for auditing.
     */
    public function __construct(
        public string $provider,
        public PaymentStatus $status,
        public ?string $reference = null,
        public array $payload = [],
    ) {}

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }
}
