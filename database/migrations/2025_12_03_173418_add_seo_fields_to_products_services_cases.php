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
        // Добавляем SEO поля в таблицу products
        Schema::table('products', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('slug')->comment('SEO заголовок (meta title)');
            $table->text('seo_description')->nullable()->after('seo_title')->comment('SEO описание (meta description)');
            $table->string('seo_keywords')->nullable()->after('seo_description')->comment('SEO ключевые слова (meta keywords)');
        });

        // Добавляем SEO поля в таблицу services
        Schema::table('services', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('slug')->comment('SEO заголовок (meta title)');
            $table->text('seo_description')->nullable()->after('seo_title')->comment('SEO описание (meta description)');
            $table->string('seo_keywords')->nullable()->after('seo_description')->comment('SEO ключевые слова (meta keywords)');
        });

        // Добавляем SEO поля в таблицу cases
        Schema::table('cases', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('slug')->comment('SEO заголовок (meta title)');
            $table->text('seo_description')->nullable()->after('seo_title')->comment('SEO описание (meta description)');
            $table->string('seo_keywords')->nullable()->after('seo_description')->comment('SEO ключевые слова (meta keywords)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем SEO поля из таблицы products
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'seo_keywords']);
        });

        // Удаляем SEO поля из таблицы services
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'seo_keywords']);
        });

        // Удаляем SEO поля из таблицы cases
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'seo_keywords']);
        });
    }
};
