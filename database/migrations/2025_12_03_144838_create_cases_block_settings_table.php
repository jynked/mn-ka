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
        Schema::create('cases_block_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('Заголовок блока');
            $table->boolean('is_active')->default(true)->comment('Активен ли блок на главной странице');
            $table->json('case_ids')->nullable()->comment('Массив ID кейсов для отображения (максимум 2)');
            $table->json('additional_settings')->nullable()->comment('Дополнительные настройки блока');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases_block_settings');
    }
};
