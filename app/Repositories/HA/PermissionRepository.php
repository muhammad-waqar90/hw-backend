<?php


namespace App\Repositories\HA;


use App\Models\PermGroup;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PermissionRepository
{

    private PermGroup $permGroup;
    private Permission $permission;

    public function __construct(PermGroup $permGroup, Permission $permission)
    {
        $this->permGroup = $permGroup;
        $this->permission = $permission;
    }

    public function getPermissionList($searchText = null)
    {
        return $this->permission
            ->when($searchText, function ($query, $searchText) {
                return $query->where('display_name', 'LIKE', "%$searchText%");
            })
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function createPermGroup($name, $description)
    {
        return $this->permGroup->create([
            'name'          => $name,
            'description'   => $description
        ]);
    }

    public function updatePermGroup($id, $name, $description)
    {
        $permGroup = $this->permGroup->findOrFail($id);
        $permGroup->name = $name;
        $permGroup->description = $description;
        $permGroup->save();

        return $permGroup;
    }

    public function getPermGroupList($searchText = null)
    {
        return $this->permGroup
            ->select('id', 'name')
            ->when($searchText, function ($query, $searchText) {
                return $query->where('name', 'LIKE', "%$searchText%");
            })
            ->orderBy('id', 'DESC')
            ->withCount('users')
            ->withCount('permissions')
            ->paginate(20);
    }

    public function getPermGroup($id)
    {
        return $this->permGroup->where('id', $id)
            ->with('users', function ($query) {
                $query
                    ->select('users.id','users.name')
                    ->with('adminProfile');
            })
            ->with('permissions')
            ->first();
    }

    public function updatePermGroupUsers($permGroupId, Array $users)
    {
        $this->clearPermGroupUsers($permGroupId);
        foreach($users as $userId) {
            DB::table('perm_group_user')->insert([
                'perm_group_id' => $permGroupId,
                'user_id'       => $userId,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now()
            ]);
        }
    }

    public function updatePermGroupPermissions($permGroupId, Array $permissions)
    {
        $this->clearPermGroupPermissions($permGroupId);
        foreach($permissions as $permissionId) {
            DB::table('perm_group_permission')->insert([
                'perm_group_id' => $permGroupId,
                'permission_id' => $permissionId,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now()
            ]);
        }
    }

    public function deletePermGroup($id)
    {
        $this->permGroup->where('id', $id)->delete();
    }

    public function clearPermGroupUsers($permGroupId)
    {
        DB::table('perm_group_user')->where('perm_group_id', $permGroupId)->delete();
    }

    public function clearPermGroupPermissions($permGroupId)
    {
        DB::table('perm_group_permission')->where('perm_group_id', $permGroupId)->delete();
    }

    public function getUserPermissionIds($userId)
    {
        return $this->userPermissionsQuery($userId)
            ->get()
            ->pluck('id');
    }

    public function hasUserPermissionIds($userId, $permissionId)
    {
        return $this->userPermissionsQuery($userId)
            ->where('permissions.id', $permissionId)
            ->exists();
    }

    private function userPermissionsQuery($userId)
    {
        return $this->permission
            ->select('permissions.id as id')
            ->join('perm_group_permission as pgp', 'pgp.permission_id', '=', 'permissions.id')
            ->join('perm_group_user as pgu', function($query) use ($userId)
            {
                $query->on('pgu.perm_group_id', '=', 'pgp.perm_group_id')
                    ->where('pgu.user_id', $userId);
            });
    }
}
