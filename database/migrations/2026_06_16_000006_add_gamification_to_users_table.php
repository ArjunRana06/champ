<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('xp')->default(0);
            $table->integer('level')->default(1);
            $table->integer('streak')->default(0);
            $table->date('last_active_date')->nullable();
            $table->string('badges')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'level', 'streak', 'last_active_date', 'badges']);
        });
    }
};
