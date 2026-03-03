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
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->comment('ID продукта');
            $table->string('name')->comment('Имя клиента');
            $table->string('phone')->comment('Телефон клиента');
            $table->text('comment')->nullable()->comment('Комментарий клиента');
            $table->json('services')->nullable()->comment('Выбранные услуги (JSON массив)');
            $table->string('status')->default('new')->comment('Статус заявки: new, in_progress, completed, cancelled, rejected');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->comment('Кто обрабатывает заявку');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Кто создал заявку (если из админки)');
            $table->text('notes')->nullable()->comment('Заметки администратора');
            $table->timestamp('completed_at')->nullable()->comment('Дата завершения обработки');
            $table->timestamps();
            
            $table->index('status');
            $table->index('assigned_to');
            $table->index('product_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_requests');
    }
};
