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
        Schema::table('ebooks', function (Blueprint $table) {
            DB::table('ebooks')->truncate();

            $table->dropForeign(['course_module_id']);
            $table->dropUnique(['course_module_id']);
            $table->dropColumn(['course_module_id']);

            $table->foreignId('lesson_id')->after('id')
                ->unique()
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ebooks', function (Blueprint $table) {
            DB::table('ebooks')->truncate();

            $table->dropForeign(['lesson_id']);
            $table->dropUnique(['lesson_id']);
            $table->dropColumn(['lesson_id']);

            $table->foreignId('course_module_id')->after('id')
                ->unique()
                ->constrained()
                ->onDelete('cascade');
        });
    }
};
