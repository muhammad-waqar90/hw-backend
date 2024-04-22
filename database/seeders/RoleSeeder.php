<?php

namespace Database\Seeders;

use App\DataObject\RoleData;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::updateOrCreate(
            ['id' => RoleData::INDEPENDENT_USER],
            [
                'name' => 'independentUser',
                'display_name' => 'Independent User',
            ]
        );
        Role::updateOrCreate(
            ['id' => RoleData::INSTITUTIONAL_USER],
            [
                'name' => 'institutionalUser',
                'display_name' => 'Institutional User',
            ]
        );
        Role::updateOrCreate(
            ['id' => RoleData::ADMIN],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
            ]
        );
        Role::updateOrCreate(
            ['id' => RoleData::HEAD_ADMIN],
            [
                'name' => 'headAdmin',
                'display_name' => 'Head Admin',
            ]
        );
        Role::updateOrCreate(
            ['id' => RoleData::MASTER_ADMIN],
            [
                'name' => 'masterAdmin',
                'display_name' => 'Master Admin',
            ]
        );
    }
}
