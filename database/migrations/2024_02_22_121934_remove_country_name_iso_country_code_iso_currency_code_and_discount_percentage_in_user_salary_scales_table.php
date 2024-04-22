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
        Schema::table('user_salary_scales', function (Blueprint $table) {
            $table->dropColumn('country_name');
            $table->dropColumn('iso_country_code');
            $table->dropColumn('iso_currency_code');
            $table->dropColumn('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_salary_scales', function (Blueprint $table) {
            $table->string('country_name')->nullable();
            $table->string('iso_country_code')->nullable();
            $table->string('iso_currency_code')->nullable();
            $table->integer('discount_percentage')->nullable();
        });
    }
};
