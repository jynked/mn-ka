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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->integer('order')->default(0)->comment('Порядок вопроса в квизе');
            $table->string('question_type')->comment('Тип вопроса: images-collect, selects, inputs, forms, thank');
            $table->text('question_text')->nullable()->comment('Текст вопроса');
            $table->json('question_data')->nullable()->comment('Данные вопроса (selects, label, placeholder, child и т.д.)');
            $table->boolean('is_active')->default(true)->comment('Активен ли вопрос');
            $table->timestamps();
            
            $table->index(['quiz_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
