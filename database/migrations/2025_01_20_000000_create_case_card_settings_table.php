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
        Schema::create('case_card_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_title')->nullable()->comment('Заголовок страницы кейсов');
            $table->text('page_description')->nullable()->comment('Описание страницы кейсов');
            $table->integer('items_per_page')->default(6)->comment('Количество карточек на странице');
            $table->boolean('show_filters')->default(true)->comment('Показывать фильтры');
            $table->boolean('show_breadcrumbs')->default(true)->comment('Показывать хлебные крошки');
            $table->string('card_aspect_ratio')->default('16/10')->comment('Соотношение сторон карточки');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_card_settings');
    }
};



