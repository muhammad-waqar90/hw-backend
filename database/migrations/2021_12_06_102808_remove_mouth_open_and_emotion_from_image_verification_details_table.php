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
        Schema::table('image_verification_details', function (Blueprint $table) {
            $table->dropColumn('mouth_open');
            $table->dropColumn('emotion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('image_verification_details', function (Blueprint $table) {
            $table->boolean('mouth_open');
            $table->string('emotion')->nullable();
        });
    }
};
