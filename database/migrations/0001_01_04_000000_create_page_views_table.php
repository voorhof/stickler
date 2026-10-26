<?php

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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path', 255);
            $table->string('route_name', 100)->nullable();
            $table->string('referrer_host', 255)->nullable();
            $table->char('visitor_hash', 64);
            $table->date('viewed_on')->index();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->boolean('is_bot')->default(false)->index();
            $table->index(['viewed_on', 'is_bot', 'visitor_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
