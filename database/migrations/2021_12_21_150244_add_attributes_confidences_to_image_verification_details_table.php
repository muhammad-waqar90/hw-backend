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
            $table->double('smile_confidence', 8, 2)->after('smile');
            $table->double('eye_glasses_confidence', 8, 2)->after('eye_glasses');
            $table->double('sun_glasses_confidence', 8, 2)->after('sun_glasses');
            $table->double('gender_confidence', 8, 2)->after('gender');
            $table->double('beard_confidence', 8, 2)->after('beard');
            $table->double('mustache_confidence', 8, 2)->after('mustache');
            $table->double('eyes_open_confidence', 8, 2)->after('eyes_open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_verification_details', function (Blueprint $table) {
            $table->dropColumn('smile_confidence');
            $table->dropColumn('eye_glasses_confidence');
            $table->dropColumn('sun_glasses_confidence');
            $table->dropColumn('gender_confidence');
            $table->dropColumn('beard_confidence');
            $table->dropColumn('mustache_confidence');
            $table->dropColumn('eyes_open_confidence');
        });
    }
};
