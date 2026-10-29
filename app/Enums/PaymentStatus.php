<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Payment status tracking the financial state of an order.
 */
enum PaymentStatus: string implements HasLabel
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return __(ucfirst($this->value));
    }
}
