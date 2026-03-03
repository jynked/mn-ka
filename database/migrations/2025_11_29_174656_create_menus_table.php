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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('url')->nullable();
            $table->enum('type', ['header', 'footer', 'burger'])->default('header');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('additional_data')->nullable(); // Для хранения дополнительных данных (иконки, цвета и т.д.)
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
            $table->index('type');
            $table->index('order');
            $table->index(['type', 'is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
