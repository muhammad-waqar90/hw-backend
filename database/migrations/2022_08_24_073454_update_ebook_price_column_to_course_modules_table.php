<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->float('ebook_price', 8, 2)->default(0)->change();
            DB::table('course_modules')
                ->where('ebook_price', null)
                ->update([
                    'ebook_price' => 0,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->float('ebook_price', 8, 2)->nullable()->change();
        });
    }
};
