<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('true_false_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('document_id')->nullable()->constrained()->onDelete('set null');
            $table->text('statement');
            $table->boolean('correct_answer');
            $table->text('explanation')->nullable();
            $table->string('difficulty')->default('medium');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('true_false_questions');
    }
};
