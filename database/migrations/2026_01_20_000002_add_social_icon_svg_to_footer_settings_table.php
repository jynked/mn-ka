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
            $table->text('vk_icon_svg')->nullable()->after('vk_icon_id');
            $table->text('instagram_icon_svg')->nullable()->after('instagram_icon_id');
            $table->text('telegram_icon_svg')->nullable()->after('telegram_icon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn(['vk_icon_svg', 'instagram_icon_svg', 'telegram_icon_svg']);
        });
    }
};
