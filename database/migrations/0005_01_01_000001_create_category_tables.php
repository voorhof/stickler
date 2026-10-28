<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title')->unique();
            $table->boolean('for_pages')->nullable()->default(false);
            $table->boolean('for_posts')->nullable()->default(false);
            $table->boolean('for_products')->nullable()->default(false);
            $table->boolean('for_projects')->nullable()->default(false);
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignIdFor(User::class, 'created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->unsignedBigInteger('categorizable_id');
            $table->string('categorizable_type');
            $table->timestamps();

            $table->primary(['category_id', 'categorizable_id', 'categorizable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('categorizables');
    }
};
