<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('emotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('emotion');          // happy, sad, angry, excited, etc.
            $table->float('confidence_score')->nullable();
            $table->timestamps();

            $table->index('activity_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('emotions');
    }
};
