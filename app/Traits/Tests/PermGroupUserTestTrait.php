<?php

namespace App\Traits\Tests;

use App\Models\User;
use App\Models\PermGroup;
use App\Models\Permission;

use App\DataObject\PermissionData;
use Illuminate\Support\Facades\DB;


trait PermGroupUserTestTrait {
    public function permissionsSeeder($numOfGroupsAndUsers = 15) {
        $permGroups = PermGroup::factory($numOfGroupsAndUsers)->create();
        $permissions = array_values(PermissionData::getConstants());
        $searchPermissions = Permission::factory(5)->withDisplayName('factory_permission')->create();
        $users = User::factory($numOfGroupsAndUsers)->verified()->admin()->create();

        for($i=0; $i<$numOfGroupsAndUsers; $i++){
            $permGroupPermissionData[] = ['perm_group_id' => $permGroups[$i]->id, 'permission_id' => $permissions[$i]];
            $permGroupUser[] = ['perm_group_id' => $permGroups[$i]->id, 'user_id' => $users->pluck('id')->random()];
        }

        DB::table('perm_group_permission')->insert($permGroupPermissionData);
        DB::table('perm_group_user')->insert($permGroupUser);

        $data = new \stdClass();

        $data->permGroups = $permGroups;
        $data->permissions = $permissions;
        $data->searchPermissions = $searchPermissions;
        $data->users = $users;

        return $data;
    }

    public function assignAllPermissionToUser($user) {
        $allPermissionsGroup = PermGroup::factory()->create();

        $permissions = array_values(PermissionData::getConstants());

        for($i=0; $i< count($permissions); $i++){
            $permGroupPermissionData[] = ['perm_group_id' => $allPermissionsGroup->id, 'permission_id' => $permissions[$i]];
        }

        DB::table('perm_group_permission')->insert($permGroupPermissionData);

        DB::table('perm_group_user')->insert([
            ['perm_group_id' => $allPermissionsGroup->id, 'user_id' => $user->id],
        ]);
    }

    public function assignFAQCategoryManagementPermissionToUser($user) {
        $CategoryManagementPermissionsGroup = PermGroup::factory()->create();

        DB::table('perm_group_permission')->insert([
            ['perm_group_id' => $CategoryManagementPermissionsGroup->id, 'permission_id' => PermissionData::FAQ_MANAGEMENT],
            ['perm_group_id' => $CategoryManagementPermissionsGroup->id, 'permission_id' => PermissionData::FAQ_CATEGORY_MANAGEMENT],
        ]);

        DB::table('perm_group_user')->insert([
            ['perm_group_id' => $CategoryManagementPermissionsGroup->id, 'user_id' => $user->id],
        ]);
    }

    public function assignSystemPermissionToUser($user) {
        $systemPermissionsGroup = PermGroup::factory()->create();

        DB::table('perm_group_permission')->insert([['perm_group_id' => $systemPermissionsGroup->id, 'permission_id' => PermissionData::TICKET_SYSTEM_MANAGEMENT]]);

        DB::table('perm_group_user')->where('user_id', $user->id)->delete();
        DB::table('perm_group_user')->insert([['perm_group_id' => $systemPermissionsGroup->id, 'user_id' => $user->id]]);
    }
}
