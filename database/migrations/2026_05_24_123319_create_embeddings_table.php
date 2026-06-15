<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_chunk_id')->constrained()->onDelete('cascade');
            $table->json('embedding'); // store embedding as JSON array
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('embeddings');
    }
};
