<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['text', 'voice', 'video', 'image', 'emoji', 'emotion'])->default('text');
            $table->json('content')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('session_id')->nullable(); // no foreign key yet
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
};
