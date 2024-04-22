<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exam_accesses', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts_left')->after('quiz_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exam_accesses', function (Blueprint $table) {
            $table->dropColumn('attempts_left');
        });
    }
};
