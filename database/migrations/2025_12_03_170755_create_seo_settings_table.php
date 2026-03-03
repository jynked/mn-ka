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
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            
            // Глобальные настройки
            $table->string('site_name')->nullable()->comment('Название сайта');
            $table->text('site_description')->nullable()->comment('Описание сайта');
            $table->string('site_keywords')->nullable()->comment('Ключевые слова сайта');
            $table->string('default_og_image')->nullable()->comment('Изображение по умолчанию для Open Graph');
            
            // Open Graph
            $table->string('og_type')->default('website')->comment('Тип контента Open Graph');
            $table->string('og_site_name')->nullable()->comment('Название сайта для OG');
            
            // Twitter Cards
            $table->string('twitter_card')->default('summary_large_image')->comment('Тип Twitter Card');
            $table->string('twitter_site')->nullable()->comment('Twitter аккаунт сайта');
            $table->string('twitter_creator')->nullable()->comment('Twitter аккаунт создателя');
            
            // Контактная информация (для Schema.org)
            $table->string('organization_name')->nullable()->comment('Название организации');
            $table->string('organization_logo')->nullable()->comment('Лого организации');
            $table->string('organization_phone')->nullable()->comment('Телефон организации');
            $table->string('organization_email')->nullable()->comment('Email организации');
            $table->text('organization_address')->nullable()->comment('Адрес организации');
            
            // Настройки индексации
            $table->boolean('allow_indexing')->default(true)->comment('Разрешить индексацию');
            $table->text('robots_txt')->nullable()->comment('Содержимое robots.txt');
            
            // JSON-LD дополнительные данные
            $table->json('additional_schema')->nullable()->comment('Дополнительная Schema.org разметка');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
