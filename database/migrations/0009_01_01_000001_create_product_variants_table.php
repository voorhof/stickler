<?php

use App\Models\Product;
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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class, 'product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->unique()->nullable();
            $table->integer('price')->nullable();
            $table->integer('discount_price')->nullable();
            $table->boolean('is_discounted')->default(false);
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();
            $table->foreignIdFor(User::class, 'created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
