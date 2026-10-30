<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Random\RandomException;

class OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::isPublicPublished()->with('variants.options')->get();
        $users = User::factory()->count(5)->create();

        if ($products->isEmpty()) {
            return;
        }

        // Pending orders (3)
        $this->createOrdersWithItems(3, $products, $users, []);

        // Confirmed orders (2)
        $this->createOrdersWithItems(2, $products, $users, ['confirmed']);

        // Processing orders (2)
        $this->createOrdersWithItems(2, $products, $users, ['processing', 'paid']);

        // Shipped orders (2)
        $this->createOrdersWithItems(2, $products, $users, ['shipped', 'paid']);

        // Delivered orders (3)
        $this->createOrdersWithItems(3, $products, $users, ['delivered', 'paid']);

        // Cancelled orders (1)
        $this->createOrdersWithItems(1, $products, $users, ['cancelled']);

        // Refunded order (1)
        $this->createOrdersWithItems(1, $products, $users, ['delivered', 'refunded']);

        // Guest orders (2)
        $this->createGuestOrdersWithItems(2, $products);
    }

    /**
     * Create orders with items for registered users.
     */
    protected function createOrdersWithItems(int $count, $products, $users, array $states): void
    {
        for ($i = 0; $i < $count; $i++) {
            $factory = Order::factory();

            foreach ($states as $state) {
                $factory = $factory->{$state}();
            }

            $userId = $users->random()->id;

            $order = $factory->create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),
                'created_by_user_id' => $userId,
                'updated_by_user_id' => $userId,
            ]);

            $itemCount = random_int(1, 4);
            for ($j = 0; $j < $itemCount; $j++) {
                $order->orderItems()->create($this->orderItemAttributes($userId));
            }

            $this->assignProductsToItems($order->load('orderItems'), $products);
        }
    }

    /**
     * Create guest orders without a user.
     */
    protected function createGuestOrdersWithItems(int $count, $products): void
    {
        for ($i = 0; $i < $count; $i++) {
            $order = Order::factory()
                ->guest()
                ->create(['order_number' => $this->generateOrderNumber()]);

            $itemCount = random_int(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $order->orderItems()->create($this->orderItemAttributes());
            }

            $this->assignProductsToItems($order->load('orderItems'), $products);
        }
    }

    /**
     * Generate random order item attributes without nested factories.
     *
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    protected function orderItemAttributes($userId = null): array
    {
        $quantity = random_int(1, 10);
        $unitPrice = random_int(1000, 50000);

        return [
            'product_id' => null,
            'product_variant_id' => null,
            'product_name' => fake()->words(3, true),
            'product_variant_label' => null,
            'sku' => fake()->ean8(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'created_by_user_id' => $userId,
            'updated_by_user_id' => $userId,
        ];
    }

    /**
     * Generate a unique order number like ORD-2026-00001.
     */
    protected function generateOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'ORD-'.$year.'-';

        $count = Order::where('order_number', 'like', $prefix.'%')->count();
        $nextNumber = $count + 1;
        $sequence = mb_str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);

        return $prefix.$sequence;
    }

    /**
     * Assign existing products to order items with snapshot data.
     */
    protected function assignProductsToItems(Order $order, $products): void
    {
        $order->orderItems->each(function (OrderItem $item) use ($products) {
            $product = $products->random();

            $item->update([
                'product_id' => $product->id,
                'product_name' => $product->title,
                'unit_price' => $product->price,
                'sku' => $product->sku,
                'total_price' => $product->price * $item->quantity,
            ]);

            if ($product->variants->isNotEmpty()) {
                $variant = $product->variants->random();
                $label = $variant->options->pluck('name')->implode(' / ');
                $item->update([
                    'product_variant_id' => $variant->id,
                    'product_variant_label' => $label ?: null,
                ]);
            }
        });
    }
}
