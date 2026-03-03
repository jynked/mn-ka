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
        Schema::create('about_settings', function (Blueprint $table) {
            $table->id();
            $table->string('banner_image')->nullable()->comment('Изображение баннера');
            $table->boolean('banner_overlay')->default(false)->comment('Наложение на баннер');
            $table->text('description')->nullable()->comment('Описание о компании');
            $table->json('statistics')->nullable()->comment('Статистика');
            $table->json('clients')->nullable()->comment('Кому мы помогаем');
            $table->json('team')->nullable()->comment('Команда');
            $table->json('benefits')->nullable()->comment('Преимущества');
            $table->timestamps();
        });

        // Создаем запись по умолчанию
        \App\Models\AboutSettings::create([
            'banner_image' => null,
            'banner_overlay' => false,
            'description' => null,
            'statistics' => [],
            'clients' => [],
            'team' => [],
            'benefits' => [],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_settings');
    }
};
