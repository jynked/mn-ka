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
        Schema::table('product_requests', function (Blueprint $table) {
            // Делаем product_id nullable для общих заявок обратной связи
            $table->foreignId('product_id')->nullable()->change();
            
            // Делаем phone nullable, так как в формах обратной связи телефон может быть необязательным
            $table->string('phone')->nullable()->change();
            
            // Добавляем поле email для обратной связи
            $table->string('email')->nullable()->after('phone')->comment('Email клиента');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            // Восстанавливаем обратно (но это может вызвать проблемы, если есть записи с null)
            $table->foreignId('product_id')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->dropColumn('email');
        });
    }
};
