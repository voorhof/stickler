<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cart::class, 'cart_id')->constrained('carts')->cascadeOnDelete();

            $table->foreignIdFor(Product::class, 'product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignIdFor(ProductVariant::class, 'product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            $table->string('product_name');
            $table->string('product_variant_label')->nullable();
            $table->string('sku')->nullable();

            $table->integer('quantity');
            $table->integer('unit_price');
            $table->integer('total_price')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreignIdFor(User::class, 'created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->index('cart_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
