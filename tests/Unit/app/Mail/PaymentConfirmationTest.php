<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\PaymentConfirmation;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;

beforeEach(function () {
    config(['logging.channels.single.path' => storage_path('logs/testing.log')]);
});

describe('mailable content', function () {
    test('it implements should queue for asynchronous processing', function () {
        $order = Order::factory()->create();
        $mailable = new PaymentConfirmation($order);

        expect($mailable)->toBeInstanceOf(ShouldQueue::class);
    });

    test('it sends the payment confirmation email with properly formatted prices', function () {
        $order = Order::factory()->create([
            'order_number' => 'ORD-001',
            'total' => 25.00,
            'payment_status' => PaymentStatus::Paid,
            'status' => OrderStatus::Confirmed,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_name' => 'Product A',
            'product_variant_label' => 'Red / Large',
            'quantity' => 2,
            'total_price' => 15.00,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_name' => 'Product B',
            'product_variant_label' => null,
            'quantity' => 1,
            'total_price' => 10.00,
        ]);
        $order->load('orderItems');

        $mailable = new PaymentConfirmation($order);

        $mailable
            ->assertFrom(config('mail.from.address'))
            ->assertHasSubject(__('Payment confirmation'))

            ->assertSeeInHtml(__('Thank you for your order!'))
            ->assertSeeInHtml(__('Your payment for order'))
            ->assertSeeInHtml($order->order_number)
            ->assertSeeInHtml(__('has been confirmed.'))
            ->assertSeeInHtml(__('Order total'))
            ->assertSeeInHtml(number_format($order->total, 2))
            ->assertSeeInHtml(__('Product'))
            ->assertSeeInHtml(__('Quantity'))
            ->assertSeeInHtml(__('Total'))
            ->assertSeeInHtml('Product A')
            ->assertSeeInHtml('Red / Large')
            ->assertSeeInHtml('15.00')
            ->assertSeeInHtml('Product B')
            ->assertSeeInHtml('10.00')
            ->assertSeeInHtml(__('Your order will be processed shortly.'))
            ->assertSeeInHtml(__('Thank you'))
            ->assertSeeInHtml(config('app.name'))

            ->assertSeeInText(__('Thank you for your order!'))
            ->assertSeeInText(__('Your payment for order'))
            ->assertSeeInText($order->order_number)
            ->assertSeeInText(__('has been confirmed.'))
            ->assertSeeInText(__('Order total'))
            ->assertSeeInText(number_format($order->total, 2))
            ->assertSeeInText('Product A')
            ->assertSeeInText('Red / Large')
            ->assertSeeInText('15.00')
            ->assertSeeInText('Product B')
            ->assertSeeInText('10.00')
            ->assertSeeInText(__('Quantity'))
            ->assertSeeInText(__('Total'))
            ->assertSeeInText(__('Your order will be processed shortly.'))
            ->assertSeeInText(__('Thank you'))
            ->assertSeeInText(config('app.name'));
    });
});
