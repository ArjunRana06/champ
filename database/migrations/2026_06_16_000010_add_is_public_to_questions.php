<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mcqs', fn(Blueprint $t) => $t->boolean('is_public')->default(false)->after('user_id'));
        Schema::table('true_false_questions', fn(Blueprint $t) => $t->boolean('is_public')->default(false)->after('user_id'));
        Schema::table('short_answers', fn(Blueprint $t) => $t->boolean('is_public')->default(false)->after('user_id'));
        Schema::table('fill_blanks', fn(Blueprint $t) => $t->boolean('is_public')->default(false)->after('user_id'));
        Schema::table('matching_questions', fn(Blueprint $t) => $t->boolean('is_public')->default(false)->after('user_id'));
        Schema::table('flashcards', fn(Blueprint $t) => $t->boolean('is_public')->default(false)->after('user_id'));
    }

    public function down(): void
    {
        Schema::table('mcqs', fn(Blueprint $t) => $t->dropColumn('is_public'));
        Schema::table('true_false_questions', fn(Blueprint $t) => $t->dropColumn('is_public'));
        Schema::table('short_answers', fn(Blueprint $t) => $t->dropColumn('is_public'));
        Schema::table('fill_blanks', fn(Blueprint $t) => $t->dropColumn('is_public'));
        Schema::table('matching_questions', fn(Blueprint $t) => $t->dropColumn('is_public'));
        Schema::table('flashcards', fn(Blueprint $t) => $t->dropColumn('is_public'));
    }
};
