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
        Schema::create('home_page_settings', function (Blueprint $table) {
            $table->id();
            
            // HERO-блок
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_button_text')->nullable();
            $table->string('hero_button_link')->nullable();
            
            // Блок "Подберите решение"
            $table->string('select_title')->nullable();
            $table->text('select_subtitle')->nullable();
            
            // Блок "Как мы работаем"
            $table->string('work_title')->nullable();
            $table->json('work_items')->nullable(); // массив объектов { title, text }
            $table->string('work_button_text')->nullable();
            $table->string('work_button_link')->nullable();
            
            // Блок "Частые вопросы"
            $table->string('faq_title')->nullable();
            $table->json('faq_items')->nullable(); // массив объектов { question, answer }
            
            // Блок "Почему выбирают нас"
            $table->string('benefits_title')->nullable();
            $table->json('benefits_items')->nullable(); // массив объектов { title, short_text, icon? }
            
            // Блок "Остались вопросы"
            $table->string('contact_title')->nullable();
            $table->text('contact_subtitle')->nullable();
            $table->text('contact_form_hint_text')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_page_settings');
    }
};
