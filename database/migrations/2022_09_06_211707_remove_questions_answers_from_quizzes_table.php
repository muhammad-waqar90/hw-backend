<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('questions');
            $table->dropColumn('answers');
            $table->dropColumn('hard_questions');
            $table->dropColumn('hard_answers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->json('questions')->after('price')->nullable();
            $table->json('answers')->after('questions')->nullable();
            $table->json('hard_questions')->after('answers')->nullable();
            $table->json('hard_answers')->after('hard_questions')->nullable();
        });
    }
};
