<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('intro');
            $table->longText('content')->nullable();

            $table->string('sku')->unique()->nullable();
            $table->integer('price');
            $table->integer('discount_price')->nullable();
            $table->boolean('is_discounted')->default(false);
            $table->integer('stock_quantity')->default(0);
            $table->string('website_title')->nullable();
            $table->string('website_url')->nullable();

            $table->unsignedInteger('order_column')->nullable()->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignIdFor(User::class, 'created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['deleted_at', 'published_at', 'order_column']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
