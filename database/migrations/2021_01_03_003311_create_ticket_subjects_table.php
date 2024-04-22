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
        Schema::create('ticket_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_category_id')->constrained()->onDelete('cascade');
            $table->string('name')->unique();
            $table->text('desc')->nullable();
            $table->boolean('only_logged_in_users')->default(0);
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
        Schema::dropIfExists('ticket_subjects');
    }
};
