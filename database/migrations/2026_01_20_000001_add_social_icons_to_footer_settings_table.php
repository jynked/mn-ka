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
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->foreignId('vk_icon_id')
                ->nullable()
                ->after('social_networks')
                ->constrained('media')
                ->nullOnDelete();

            $table->foreignId('instagram_icon_id')
                ->nullable()
                ->after('vk_icon_id')
                ->constrained('media')
                ->nullOnDelete();

            $table->foreignId('telegram_icon_id')
                ->nullable()
                ->after('instagram_icon_id')
                ->constrained('media')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropForeign(['vk_icon_id']);
            $table->dropForeign(['instagram_icon_id']);
            $table->dropForeign(['telegram_icon_id']);

            $table->dropColumn(['vk_icon_id', 'instagram_icon_id', 'telegram_icon_id']);
        });
    }
};

