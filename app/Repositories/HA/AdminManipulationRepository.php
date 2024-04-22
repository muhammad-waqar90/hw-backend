<?php

namespace App\Repositories\HA;

use App\DataObject\RoleData;
use App\Exceptions\NotFoundException;
use App\Models\User;
use App\Models\AdminProfile;
use App\Models\VerifyUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminManipulationRepository
{

    private User $user;
    private AdminProfile $adminProfile;

    public function __construct(User $user, AdminProfile $adminProfile)
    {
        $this->user = $user;
        $this->adminProfile = $adminProfile;
    }

    public function createAdmin($name, $firstName, $lastName, $role, Array $permGroupIds = [])
    {
        $user = $this->user->create([
            'name'              => $name,
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'role_id'           => $role,
            'password'          => bcrypt(Str::random(50)),
            'email_verified_at' => Carbon::now()
        ]);

        foreach($permGroupIds as $permGroupId) {
            DB::table('perm_group_user')->insert([
                'perm_group_id' => $permGroupId,
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        return $user;
    }

    public function createAdminProfile($userId, $email)
    {
        return $this->adminProfile->create([
            'user_id'       => $userId,
            'email'         => $email
        ]);
    }

    public function getAdmin($id, $role)
    {
        return $this->user
            ->select('users.id', 'users.name', 'users.first_name', 'users.last_name', 'users.is_enabled')
            ->where('users.id', $id)
            ->where('users.role_id', $role)
            ->with('adminProfile')
            ->with('permGroups')
            ->first();
    }

    public function updateAdmin($userId, Array $permGroupIds)
    {
        $user = $this->user->where('id', $userId)
            ->where('role_id', RoleData::ADMIN)
            ->first();
        if(!$user)
            throw new NotFoundException();

        DB::table('perm_group_user')->where('user_id', $userId)->delete();

        foreach($permGroupIds as $permGroupId) {
            DB::table('perm_group_user')->insert([
                'perm_group_id' => $permGroupId,
                'user_id' => $userId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }

    public function deleteAdmin($admin)
    {
        return $admin->forceDelete();
    }

    public function getAdminCount($role)
    {
        return $this->user->where('role_id', $role)
            ->count();
    }

    public function activateAdmin($id)
    {
        return $this->user->where('id', $id)
            ->where('role_id', RoleData::ADMIN)
            ->update([
                'is_enabled' => 1
            ]);
    }

    public function deactivateAdmin($id)
    {
        return $this->user->where('id', $id)
            ->where('role_id', RoleData::ADMIN)
            ->update([
                'is_enabled' => 0
            ]);
    }
}
