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
        Schema::create('coupon_restrictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('entity_id');
            $table->string('entity_type');
            $table->timestamps();

            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons')
                ->onDelete('cascade');

            $table->unique(['coupon_id', 'entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_restrictions');
    }
};
