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
        Schema::create('user_salary_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discounted_country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discounted_country_range_id')->constrained()->cascadeOnDelete();
            $table->string('country_name')->nullable();
            $table->string('iso_country_code')->nullable();
            $table->string('iso_currency_code')->nullable();
            $table->integer('discount_percentage')->nullable();
            $table->boolean('declaration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_salary_scales');
    }
};
