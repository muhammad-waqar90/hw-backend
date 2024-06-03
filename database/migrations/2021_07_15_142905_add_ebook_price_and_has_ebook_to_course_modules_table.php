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
        Schema::table('course_modules', function (Blueprint $table) {
            $table->boolean('has_ebook')->after('description')
                ->default(0);
            $table->float('ebook_price', 8, 2)->after('has_ebook')
                ->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropColumn('has_ebook');
            $table->dropColumn('ebook_price');
        });
    }
};
