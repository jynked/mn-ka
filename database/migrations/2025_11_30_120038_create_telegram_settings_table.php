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
        Schema::create('telegram_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bot_token')->nullable()->comment('Токен бота от BotFather');
            $table->string('bot_name')->nullable()->comment('Имя бота');
            $table->string('chat_id')->nullable()->comment('ID чата для отправки уведомлений');
            $table->string('webhook_url')->nullable()->comment('URL для webhook');
            $table->boolean('is_enabled')->default(false)->comment('Включен ли бот');
            $table->boolean('send_notifications')->default(true)->comment('Отправлять уведомления');
            $table->boolean('send_errors')->default(true)->comment('Отправлять критические ошибки');
            $table->string('parse_mode')->default('HTML')->comment('Режим парсинга (HTML, Markdown, MarkdownV2)');
            $table->boolean('disable_notification')->default(false)->comment('Отправлять без звука');
            $table->integer('reply_to_message_id')->nullable()->comment('ID сообщения для ответа');
            $table->boolean('disable_web_page_preview')->default(false)->comment('Отключить превью ссылок');
            $table->json('additional_settings')->nullable()->comment('Дополнительные настройки');
            $table->timestamps();
        });

        // Создаем запись по умолчанию
        \App\Models\TelegramSettings::create([
            'is_enabled' => false,
            'send_notifications' => true,
            'send_errors' => true,
            'parse_mode' => 'HTML',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_settings');
    }
};
