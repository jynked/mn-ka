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
        Schema::create('why_choose_us_block_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('Заголовок блока');
            $table->boolean('is_active')->default(true)->comment('Активен ли блок на главной странице');
            $table->json('items')->nullable()->comment('Массив карточек (до 6 штук)');
            $table->json('additional_settings')->nullable()->comment('Дополнительные настройки блока');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('why_choose_us_block_settings');
    }
};



