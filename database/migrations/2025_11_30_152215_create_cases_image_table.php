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
        Schema::create('cases_image', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cases_id')->constrained('cases')->onDelete('cascade');
            $table->foreignId('image_id')->constrained('media')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['cases_id', 'order']);
            $table->unique(['cases_id', 'image_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases_image');
    }
};
