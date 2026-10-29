<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Order status representing the lifecycle stage of an order.
 */
enum OrderStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return __(ucfirst($this->value));
    }
}
