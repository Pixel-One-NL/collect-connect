<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Jobs;

use App\Mail\StockBackInStockMail;
use App\Models\StockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class DispatchStockNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  list<int>  $productIds
     */
    public function __construct(public array $productIds) {}

    public function handle(): void
    {
        if ($this->productIds === []) {
            return;
        }

        StockNotification::query()
            ->whereIn('product_id', $this->productIds)
            ->whereNull('notified_at')
            ->with('product.productable')
            ->each(function (StockNotification $notification): void {
                if (! $notification->product || $notification->product->stock <= 0) {
                    return;
                }

                Mail::to($notification->email)->send(new StockBackInStockMail($notification->product));
                $notification->update(['notified_at' => now()]);
            });
    }
}
