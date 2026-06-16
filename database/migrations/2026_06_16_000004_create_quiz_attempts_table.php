<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('type'); // 'mcq', 'true-false', 'mixed'
            $table->integer('total_questions');
            $table->integer('correct_answers')->default(0);
            $table->integer('score_percentage')->default(0);
            $table->integer('time_taken_seconds')->nullable();
            $table->boolean('is_exam_mode')->default(false);
            $table->json('answers_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
