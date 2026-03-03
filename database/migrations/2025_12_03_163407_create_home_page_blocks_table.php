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
        Schema::create('home_page_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('block_key')->unique()->comment('Уникальный ключ блока (hero_banner, decisions, quiz, etc.)');
            $table->string('block_name')->comment('Название блока для отображения');
            $table->string('component_name')->comment('Название Vue компонента');
            $table->integer('order')->default(0)->comment('Порядок отображения блока');
            $table->boolean('is_active')->default(true)->comment('Активен ли блок');
            $table->json('settings')->nullable()->comment('Дополнительные настройки блока');
            $table->timestamps();
            
            $table->index('order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_page_blocks');
    }
};
