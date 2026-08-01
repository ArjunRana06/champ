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
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            Schema::table('document_chunks', function (Blueprint $table) {
                $table->fullText('content');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            Schema::table('document_chunks', function (Blueprint $table) {
                $table->dropFullText('content');
            });
        }
    }
};
