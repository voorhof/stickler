<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignIdFor(User::class, 'user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', array_column(OrderStatus::cases(), 'value'))->default(OrderStatus::Pending->value);
            $table->enum('payment_status', array_column(PaymentStatus::cases(), 'value'))->default(PaymentStatus::Unpaid->value);

            $table->integer('subtotal')->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('shipping_amount')->default(0);
            $table->integer('total')->default(0);

            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();

            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->foreignIdFor(User::class, 'created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->index('order_number');
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
