<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('resourceable');
            $table->timestamps();
            $table->unique(['study_group_id', 'resourceable_type', 'resourceable_id'], 'group_resource_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_resources');
    }
};
