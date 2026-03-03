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
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('Заголовок футера');
            $table->string('department_label')->nullable()->comment('Метка для отдела');
            $table->string('department_phone')->nullable()->comment('Телефон отдела');
            $table->string('objects_label')->nullable()->comment('Метка для объектов');
            $table->string('objects_phone')->nullable()->comment('Телефон объектов');
            $table->string('issues_label')->nullable()->comment('Метка для вопросов');
            $table->string('issues_email')->nullable()->comment('Email для вопросов');
            $table->json('social_networks')->nullable()->comment('Социальные сети (vk, instagram, telegram)');
            $table->json('menu_items')->nullable()->comment('Элементы меню футера');
            $table->string('privacy_policy_link')->nullable()->comment('Ссылка на политику конфиденциальности');
            $table->string('copyright')->nullable()->comment('Копирайт');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_settings');
    }
};



