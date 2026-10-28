<?php

namespace App\Enums;

/**
 * Order status representing the lifecycle stage of an order.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
