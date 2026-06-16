<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spaced_repetitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reviewable');
            $table->float('easiness_factor')->default(2.5);
            $table->integer('interval_days')->default(0);
            $table->integer('repetitions')->default(0);
            $table->date('next_review_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaced_repetitions');
    }
};
