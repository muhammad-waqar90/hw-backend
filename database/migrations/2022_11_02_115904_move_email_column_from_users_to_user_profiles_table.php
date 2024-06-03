<?php

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Move emails column along with data to user_profiles table
     *
     * - add email column to user_profiles
     * - dump data from users to user_profiles
     * - remove email column from users table
     */
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('email')->after('user_id');
        });

        // $this->dumpEmails(new UserProfile, new User, 'id AS user_id', 'user_id');

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->unique('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

    /**
     * Reverse the migrations.
     * Move emails column along with data to user_profiles table
     *
     * - add email column to users
     * - dump data from user_profiles to users
     * - remove email column from user_profiles table
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->after('name');
        });

        // $this->dumpEmails(new User, new UserProfile, 'user_id AS id', 'id');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

    private function dumpEmails($modelTo, $modelFrom, $selector, $index)
    {
        return Batch::update(
            $modelTo,
            $modelFrom::withoutGlobalScopes()->select($selector, 'email')->get()->toArray(),
            $index
        );
    }
};
