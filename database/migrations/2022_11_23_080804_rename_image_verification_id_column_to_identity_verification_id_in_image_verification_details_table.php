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
        Schema::table('image_verification_details', function (Blueprint $table) {
            $table->renameColumn('image_verification_id', 'identity_verification_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_verification_details', function (Blueprint $table) {
            $table->renameColumn('identity_verification_id', 'image_verification_id');
        });
    }
};
