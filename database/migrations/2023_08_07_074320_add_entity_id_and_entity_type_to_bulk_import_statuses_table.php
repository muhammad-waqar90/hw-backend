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
        Schema::table('bulk_import_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('entity_id')->after('course_id');
            $table->string('entity_type')->after('entity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulk_import_statuses', function (Blueprint $table) {
            $table->dropColumn('entity_id');
            $table->dropColumn('entity_type');
        });
    }
};
