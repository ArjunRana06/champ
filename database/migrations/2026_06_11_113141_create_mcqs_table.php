<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mcqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('document_id')->nullable()->constrained()->onDelete('set null');
            $table->text('question');
            $table->json('options');          // e.g. ["Option A", "Option B", ...]
            $table->string('correct_answer');
            $table->text('explanation')->nullable();
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mcqs');
    }
};
