<?php

namespace App\Enums;

/**
 * Payment status tracking the financial state of an order.
 */
enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Refunded = 'refunded';
}
