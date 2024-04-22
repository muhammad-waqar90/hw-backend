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
        Schema::table('lesson_faqs', function (Blueprint $table) {
            $table->string('question')->after('lesson_id');
            $table->renameColumn('content', 'answer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lesson_faqs', function (Blueprint $table) {
            $table->dropColumn('question');
            $table->renameColumn('answer', 'content');
        });
    }
};
