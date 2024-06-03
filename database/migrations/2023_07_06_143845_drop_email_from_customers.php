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
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Unique key attribute removed because It may cause problem once migration is rolled back.
     * It is assumed that email column will not be there in customers table in any case,
     * - required to use email from user_profiles table.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->after('user_id');
        });
    }
};
