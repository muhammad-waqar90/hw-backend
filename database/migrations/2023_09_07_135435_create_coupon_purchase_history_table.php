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
        Schema::create('coupon_purchase_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('purchase_history_id');
            $table->timestamps();

            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons')
                ->onDelete('cascade');

            $table->foreign('purchase_history_id')
                ->references('id')
                ->on('purchase_histories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_purchase_history');
    }
};
