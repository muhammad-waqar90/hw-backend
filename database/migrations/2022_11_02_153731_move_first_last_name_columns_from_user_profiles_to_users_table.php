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
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('name');
            $table->string('last_name')->after('first_name');
        });

        // $this->dumpFirstAndLastName(new User, new UserProfile, 'user_id AS id', 'id');

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('first_name')->after('email');
            $table->string('last_name')->after('first_name');
        });

        // $this->dumpFirstAndLastName(new UserProfile, new User, 'id AS user_id', 'user_id');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
        });
    }

    private function dumpFirstAndLastName($modelTo, $modelFrom, $selector, $index)
    {
        return Batch::update(
            $modelTo,
            $modelFrom::withoutGlobalScopes()->select($selector, 'first_name', 'last_name')->get()->toArray(),
            $index
        );
    }
};
