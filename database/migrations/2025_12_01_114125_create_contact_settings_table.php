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
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable()->comment('Телефон');
            $table->string('email')->nullable()->comment('Email');
            $table->text('address')->nullable()->comment('Адрес');
            $table->string('working_hours')->nullable()->comment('Время работы');
            $table->json('socials')->nullable()->comment('Социальные сети');
            $table->timestamps();
        });

        // Создаем запись по умолчанию
        \App\Models\ContactSettings::create([
            'phone' => null,
            'email' => null,
            'address' => null,
            'working_hours' => null,
            'socials' => [],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};
