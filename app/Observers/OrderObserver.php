<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;
use App\Services\CartService;

class OrderObserver
{
    use TracksCreatorAndUpdaterObserver;

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ($order->status === OrderStatus::Cancelled) {
            app(CartService::class)->restoreStockForOrder($order);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === OrderStatus::Cancelled && $order->getOriginal('status') !== OrderStatus::Cancelled) {
            app(CartService::class)->restoreStockForOrder($order);
        }
    }
}
