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
        Schema::table('global_notifications', function (Blueprint $table) {
            $table->timestamp('archive_at', $precision = 0)->after('action');
            $table->boolean('is_archived')->after('archive_at')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_notifications', function (Blueprint $table) {
            $table->dropColumn('archive_at');
            $table->dropColumn('is_archived');
        });
    }
};
