#!/bin/bash
# Скрипт для обновления миграции на сервере

cat > database/migrations/2025_11_08_171010_add_protected_to_folders_table.php << 'ENDOFFILE'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Проверяем существование колонок перед созданием
        if (!Schema::hasColumn('folders', 'protected')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->boolean('protected')->default(false)->after('position');
            });
        }
        
        if (!Schema::hasColumn('folders', 'is_trash')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->boolean('is_trash')->default(false)->after('protected');
            });
        }
        
        // Устанавливаем защиту для системных папок (id 1-4)
        // Используем отдельные запросы для избежания ошибки "Prepared statement needs to be re-prepared"
        DB::table('folders')->whereIn('id', [1, 2, 3, 4])->update([
            'protected' => true
        ]);
        
        // Устанавливаем is_trash отдельно для каждого ID
        DB::table('folders')->where('id', 4)->update(['is_trash' => true]);
        DB::table('folders')->whereIn('id', [1, 2, 3])->update(['is_trash' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn(['protected', 'is_trash']);
        });
    }
};
ENDOFFILE

echo "Файл обновлен. Проверяю синтаксис..."
php -l database/migrations/2025_11_08_171010_add_protected_to_folders_table.php

