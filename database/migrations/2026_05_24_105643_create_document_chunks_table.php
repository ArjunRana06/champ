<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_document_chunks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->integer('chunk_index');
            $table->longText('content');
            $table->string('vector_id')->nullable(); // ID in vector DB (Chroma/Pinecone)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_chunks');
    }
};
