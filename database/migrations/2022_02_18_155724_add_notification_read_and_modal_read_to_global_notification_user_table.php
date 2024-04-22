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
        Schema::table('global_notification_user', function (Blueprint $table) {
            $table->boolean('notification_read')->after('user_id')->nullable();
            $table->boolean('modal_read')->after('notification_read')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_notification_user', function (Blueprint $table) {
            $table->dropColumn('notification_read');
            $table->dropColumn('modal_read');
        });
    }
};
