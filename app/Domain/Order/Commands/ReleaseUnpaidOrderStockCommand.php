<?php

declare(strict_types=1);

namespace App\Domain\Order\Commands;

use App\Domain\Order\Jobs\ReleaseUnpaidOrderStockJob;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:release-unpaid-stock')]
class ReleaseUnpaidOrderStockCommand extends Command
{
    protected $description = 'Cancel unpaid pending orders past the stock reservation TTL and restock products';

    public function handle(): int
    {
        $stats = (new ReleaseUnpaidOrderStockJob)->handle();

        $this->info("Released stock for {$stats['released']} unpaid order(s).");

        return self::SUCCESS;
    }
}
