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
        Schema::table('products', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('description');
            $table->unsignedBigInteger('card_preview_image_id')->nullable()->after('icon_id');
            $table->string('page_title')->nullable()->after('short_description');
            $table->string('page_subtitle')->nullable()->after('page_title');
            $table->string('cta_text')->nullable()->after('page_subtitle');
            $table->string('cta_link')->nullable()->after('cta_text');
            
            $table->foreign('card_preview_image_id')->references('id')->on('media')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['card_preview_image_id']);
            $table->dropColumn([
                'short_description',
                'card_preview_image_id',
                'page_title',
                'page_subtitle',
                'cta_text',
                'cta_link',
            ]);
        });
    }
};
