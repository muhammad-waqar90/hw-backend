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
        Schema::table('user_salary_scales', function (Blueprint $table) {
            $table->dropColumn('country_name');
            $table->dropColumn('iso_country_code');
            $table->dropColumn('iso_currency_code');
            $table->dropColumn('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_salary_scales', function (Blueprint $table) {
            $table->string('country_name')->nullable();
            $table->string('iso_country_code')->nullable();
            $table->string('iso_currency_code')->nullable();
            $table->integer('discount_percentage')->nullable();
        });
    }
};
