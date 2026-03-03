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
        Schema::table('banners', function (Blueprint $table) {
            $table->integer('height_desktop')->nullable()->default(380)->after('button_value')->comment('Высота баннера для десктопа (px)');
            $table->integer('height_mobile')->nullable()->default(300)->after('height_desktop')->comment('Высота баннера для мобильных устройств (px)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['height_desktop', 'height_mobile']);
        });
    }
};
