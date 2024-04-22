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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id');
            $table->string('entity_type');
            $table->json('questions')->nullable();
            $table->json('answers')->nullable();
            $table->json('hard_questions')->nullable();
            $table->json('hard_answers')->nullable();
            $table->unsignedSmallInteger('duration');
            $table->unsignedTinyInteger('num_of_questions');
            $table->timestamps();

            $table->unique(['entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quizzes');
    }
};
