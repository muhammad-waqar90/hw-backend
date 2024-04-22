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
        Schema::create('discounted_country_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discounted_country_id')->constrained()->cascadeOnDelete();
            $table->string('discount_option')->nullable();
            $table->string('discount_range')->nullable();
            $table->integer('discount_percentage')->nullable();
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
        Schema::dropIfExists('discounted_country_ranges');
    }
};
