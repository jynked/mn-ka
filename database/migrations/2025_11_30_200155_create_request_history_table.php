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
        Schema::create('request_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('product_requests')->onDelete('cascade')->comment('ID заявки');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Кто выполнил действие');
            $table->string('action')->comment('Действие: created, status_changed, assigned, note_added, completed, cancelled, etc.');
            $table->string('old_status')->nullable()->comment('Предыдущий статус');
            $table->string('new_status')->nullable()->comment('Новый статус');
            $table->text('comment')->nullable()->comment('Комментарий к действию');
            $table->json('changes')->nullable()->comment('Изменения в JSON формате');
            $table->timestamps();
            
            $table->index('request_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_history');
    }
};
