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
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mailer')->default('smtp')->comment('Тип почтового сервера');
            $table->string('host')->nullable()->comment('SMTP хост');
            $table->integer('port')->nullable()->comment('SMTP порт');
            $table->string('username')->nullable()->comment('SMTP имя пользователя');
            $table->text('password')->nullable()->comment('SMTP пароль (зашифрован)');
            $table->string('encryption')->nullable()->comment('Шифрование (ssl/tls)');
            $table->string('from_email')->nullable()->comment('Email отправителя');
            $table->string('from_name')->nullable()->comment('Имя отправителя');
            $table->boolean('is_active')->default(true)->comment('Активны ли SMTP настройки');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
