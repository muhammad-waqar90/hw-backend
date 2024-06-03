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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('code', 70)->unique();
            $table->unsignedTinyInteger('value_type')->comment('discount type FLAT=1; PERCENTAGE=2;');
            $table->float('value', 8, 2, true);
            $table->unsignedTinyInteger('status')->comment('coupon status EXPIRED=0; ACTIVE=1; INACTIVE=2;');
            $table->unsignedMediumInteger('redeem_count')->default(0); // 16777215
            $table->unsignedMediumInteger('redeem_limit');
            $table->unsignedMediumInteger('redeem_limit_per_user')->default(1)->comment('how many times same user can redeem');
            $table->boolean('individual_use')->default(0)->comment('useable by one individual user only');
            // begins_at
            // expires_at
            // minimum_amount
            // maximum_amount

            /**
             * campaigns for multiple restrictions i.e:
             *
             * user_restrictions,
             * device_restrictions,
             * program_restrictions,
             * location_restrictions,
             * network_restrictions,
             * email_restrictions,
             * studio_restrictions,
             * genre_restrictions,
             * mobile_restrictions
             */
            // campaign_id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
