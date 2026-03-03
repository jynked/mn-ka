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
        Schema::create('decision_block_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Выберите решение под ваш участок');
            $table->integer('columns')->default(3)->comment('Количество колонок в сетке карточек');
            $table->boolean('is_active')->default(true);
            $table->json('additional_settings')->nullable()->comment('Дополнительные настройки блока');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decision_block_settings');
    }
};
