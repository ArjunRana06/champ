<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->fullText('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->dropFullText('content');
        });
    }
};
