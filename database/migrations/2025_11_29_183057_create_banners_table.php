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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Название баннера
            $table->string('slug')->unique(); // Уникальный идентификатор (например: home-banner)
            $table->string('background_image')->nullable(); // Путь к фоновому изображению
            $table->string('heading_1')->nullable(); // Первая строка заголовка
            $table->string('heading_2')->nullable(); // Вторая строка заголовка
            $table->text('description')->nullable(); // Описание/текст под заголовком
            $table->string('button_text')->nullable(); // Текст кнопки
            $table->enum('button_type', ['url', 'method'])->default('url'); // Тип кнопки: url или method
            $table->string('button_value')->nullable(); // URL или ID метода
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('slug');
            $table->index('is_active');
            $table->index(['slug', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
