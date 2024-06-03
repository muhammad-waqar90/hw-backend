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
        Schema::table('exam_accesses', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts_left')->after('quiz_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_accesses', function (Blueprint $table) {
            $table->dropColumn('attempts_left');
        });
    }
};
