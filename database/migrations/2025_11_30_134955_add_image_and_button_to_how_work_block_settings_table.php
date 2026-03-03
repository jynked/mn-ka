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
        Schema::table('how_work_block_settings', function (Blueprint $table) {
            $table->string('image')->nullable()->after('subtitle')->comment('Путь к изображению');
            $table->string('image_alt')->nullable()->after('image')->comment('Alt текст для изображения');
            $table->string('button_text')->nullable()->after('image_alt')->comment('Текст кнопки');
            $table->enum('button_type', ['url', 'method'])->default('url')->after('button_text')->comment('Тип кнопки');
            $table->string('button_value')->nullable()->after('button_type')->comment('URL или ID метода');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('how_work_block_settings', function (Blueprint $table) {
            $table->dropColumn(['image', 'image_alt', 'button_text', 'button_type', 'button_value']);
        });
    }
};
