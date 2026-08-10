<?php

declare(strict_types=1);

namespace App\Domain\Payment;

/**
 * Outcome a gateway reports back for a payment, independent of the order's
 * own lifecycle status.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
}
