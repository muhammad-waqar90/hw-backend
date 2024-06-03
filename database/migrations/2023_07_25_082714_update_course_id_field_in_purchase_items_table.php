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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')
                ->nullable(true)
                ->after('purchase_history_id')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')
                ->nullable(false)
                ->after('purchase_history_id')
                ->change();
        });
    }
};
