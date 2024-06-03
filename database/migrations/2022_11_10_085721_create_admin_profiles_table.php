<?php

use App\DataObject\RoleData;
use App\Models\AdminProfile;
use App\Models\User;
use App\Models\UserProfile;
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
        Schema::create('admin_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('email')->unique();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // $this->dumpAdmins();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // $admins = AdminProfile::select('user_id', 'email')->get()->toArray();
        // UserProfile::insert($admins);

        Schema::dropIfExists('admin_profiles');
    }

    private function dumpAdmins()
    {
        $data = [];
        $admins = User::withoutGlobalScopes()->select('id')
            ->whereIn('role_id', [RoleData::MASTER_ADMIN, RoleData::HEAD_ADMIN, RoleData::ADMIN])
            ->with('userProfile')
            ->get();

        foreach ($admins as $admin) {
            $payload = [
                'user_id' => $admin->userProfile->user_id,
                'email' => $admin->userProfile->email,
            ];

            array_push($data, $payload);
        }

        AdminProfile::insert($data);
        UserProfile::whereIn('user_id', $admins->pluck('id'))->delete();
    }
};
