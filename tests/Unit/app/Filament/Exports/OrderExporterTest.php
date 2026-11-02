<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Exports\OrderExporter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;

describe('exporting orders', function () {
    test('order exporter exports order successfully including all fields', function () {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 99.95,
            'tax_amount' => 20.99,
            'shipping_amount' => 4.99,
            'total' => 125.93,
            'payment_method' => 'ideal',
            'payment_provider' => 'mollie',
            'payment_provider_id' => 'tr_12345678',
            'shipping_address' => [
                'street' => 'Main Street 1',
                'city' => 'Brussels',
                'postal_code' => '1000',
                'country' => 'Belgium',
            ],
            'billing_address' => [
                'street' => 'Main Street 1',
                'city' => 'Brussels',
                'postal_code' => '1000',
                'country' => 'Belgium',
            ],
            'notes' => 'Please leave package at the door.',
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]);

        OrderItem::factory()->count(2)->create([
            'order_id' => $order->id,
        ]);

        $exportedData = OrderExporter::test()->export($order);

        expect($exportedData[0])->toBe((string) $order->id)
            ->and($exportedData[1])->toBe($order->order_number)
            ->and($exportedData[2])->toBe('John Doe')
            ->and($exportedData[3])->toBe('john@example.com')
            ->and($exportedData[4])->toBe('processing')
            ->and($exportedData[5])->toBe('paid')
            ->and($exportedData[6])->toBe('99.95')
            ->and($exportedData[7])->toBe('20.99')
            ->and($exportedData[8])->toBe('4.99')
            ->and($exportedData[9])->toBe('125.93')
            ->and($exportedData[10])->toBe('2')
            ->and($exportedData[11])->toBe('ideal')
            ->and($exportedData[12])->toBe('mollie')
            ->and($exportedData[13])->toBe('tr_12345678')
            ->and($exportedData[14])->toBe(json_encode([
                'street' => 'Main Street 1',
                'city' => 'Brussels',
                'postal_code' => '1000',
                'country' => 'Belgium',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->and($exportedData[15])->toBe(json_encode([
                'street' => 'Main Street 1',
                'city' => 'Brussels',
                'postal_code' => '1000',
                'country' => 'Belgium',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->and($exportedData[16])->toBe('Please leave package at the door.');
    });

    test('order exporter handles guest order with null user and notes', function () {
        $order = Order::factory()->create([
            'user_id' => null,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => 49.95,
            'tax_amount' => 10.49,
            'shipping_amount' => 0.00,
            'total' => 60.44,
            'payment_method' => null,
            'payment_provider' => null,
            'payment_provider_id' => null,
            'shipping_address' => [
                'street' => 'Guest Lane 5',
                'city' => 'Antwerp',
                'postal_code' => '2000',
                'country' => 'Belgium',
                'email' => 'guest@example.com',
            ],
            'billing_address' => null,
            'notes' => null,
        ]);

        $exportedData = OrderExporter::test()->export($order);

        expect($exportedData[0])->toBe((string) $order->id)
            ->and($exportedData[1])->toBe($order->order_number)
            ->and($exportedData[2])->toBeNull()
            ->and($exportedData[3])->toBeNull()
            ->and($exportedData[4])->toBe('pending')
            ->and($exportedData[5])->toBe('unpaid')
            ->and($exportedData[6])->toBe('49.95')
            ->and($exportedData[7])->toBe('10.49')
            ->and($exportedData[8])->toBe('0')
            ->and($exportedData[9])->toBe('60.44')
            ->and($exportedData[10])->toBe('0')
            ->and($exportedData[11])->toBeNull()
            ->and($exportedData[12])->toBeNull()
            ->and($exportedData[13])->toBeNull()
            ->and($exportedData[14])->toBe(json_encode([
                'street' => 'Guest Lane 5',
                'city' => 'Antwerp',
                'postal_code' => '2000',
                'country' => 'Belgium',
                'email' => 'guest@example.com',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->and($exportedData[15])->toBeNull()
            ->and($exportedData[16])->toBeNull();
    });

    test('notification body formats completed export message correctly', function () {
        $export = new Export;
        $export->successful_rows = 5;
        $export->total_rows = 5;

        $body = OrderExporter::getCompletedNotificationBody($export);

        expect($body)->toBe('Your order export has completed and 5 rows exported.');
    });
});
