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
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faq_category_id')->nullable();
            $table->string('name')->unique();
            $table->boolean('published')->index()->default(0);
            $table->timestamps();

            $table->foreign('faq_category_id')
                ->references('id')
                ->on('faq_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_categories');
    }
};
