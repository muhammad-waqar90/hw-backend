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
        Schema::table('verify_user_ages', function (Blueprint $table) {
            $table->dropColumn('parent_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verify_user_ages', function (Blueprint $table) {
            $table->string('parent_email')->after('user_id');
        });
    }
};
