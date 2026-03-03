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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Заголовок страницы');
            $table->string('slug')->unique()->comment('URL страницы (уникальный)');
            $table->text('content')->nullable()->comment('HTML контент страницы');
            $table->string('seo_title')->nullable()->comment('SEO заголовок (meta title)');
            $table->text('seo_description')->nullable()->comment('SEO описание (meta description)');
            $table->string('seo_keywords')->nullable()->comment('SEO ключевые слова (meta keywords)');
            $table->boolean('is_active')->default(true)->comment('Активна ли страница');
            $table->integer('order')->default(0)->comment('Порядок сортировки');
            $table->timestamps();
            
            $table->index('slug');
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
