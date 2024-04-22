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
        Schema::create('user_quizzes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('entity_id');
            $table->string('entity_type');
            $table->json('questions')->nullable();
            $table->json('answers')->nullable();
            $table->unsignedSmallInteger('duration');
            $table->unsignedTinyInteger('num_of_questions');
            $table->string('status');
            $table->json('user_answers')->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->timestamp('started_at');
            $table->timestamps();

            $table->unique(['user_id', 'entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_quizzes');
    }
};
