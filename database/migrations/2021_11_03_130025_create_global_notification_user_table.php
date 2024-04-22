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
        Schema::create('global_notification_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('global_notification_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('global_notification_id')->references('id')->on('global_notifications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('global_notification_user');
    }
};
