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
        Schema::create('modal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique()->comment('Тип модального окна (products, services, etc.)');
            $table->string('title')->nullable()->comment('Заголовок модального окна');
            $table->text('content')->nullable()->comment('Текст информационного сообщения');
            $table->boolean('is_active')->default(true)->comment('Активно ли модальное окно');
            $table->json('additional_settings')->nullable()->comment('Дополнительные настройки');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modal_settings');
    }
};
