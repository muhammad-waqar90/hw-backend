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
        Schema::create('discounted_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('iso_country_code')->nullable();
            $table->string('iso_currency_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounted_countries');
    }
};
