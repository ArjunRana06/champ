<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_processing_progress_to_documents_table.php
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('processing_progress')->default(0)->after('status');
            $table->string('processing_message')->nullable()->after('processing_progress');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['processing_progress', 'processing_message']);
        });
    }
};
