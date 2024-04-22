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
        Schema::create('image_verification_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_verification_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('age_range_low')->nullable();
            $table->unsignedTinyInteger('age_range_high')->nullable();
            $table->boolean('smile');
            $table->boolean('eye_glasses');
            $table->boolean('sun_glasses');
            $table->string('gender')->nullable();
            $table->boolean('beard');
            $table->boolean('mustache');
            $table->boolean('eyes_open');
            $table->boolean('mouth_open');
            $table->string('emotion')->nullable();
            $table->double('confidence', 8, 2);
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
        Schema::dropIfExists('image_verification_details');
    }
};
