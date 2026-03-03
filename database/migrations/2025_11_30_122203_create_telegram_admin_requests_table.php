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
        Schema::create('telegram_admin_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_user_id')->comment('ID пользователя в Telegram');
            $table->string('telegram_username')->nullable()->comment('Username пользователя в Telegram');
            $table->string('telegram_first_name')->nullable()->comment('Имя пользователя');
            $table->string('telegram_last_name')->nullable()->comment('Фамилия пользователя');
            $table->bigInteger('chat_id')->comment('ID чата для отправки уведомлений');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Статус заявки');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->comment('Кто одобрил заявку');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->comment('Связанный пользователь системы');
            $table->timestamp('approved_at')->nullable()->comment('Время одобрения');
            $table->text('rejection_reason')->nullable()->comment('Причина отклонения');
            $table->timestamps();
            
            $table->index('telegram_user_id');
            $table->index('chat_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_admin_requests');
    }
};
