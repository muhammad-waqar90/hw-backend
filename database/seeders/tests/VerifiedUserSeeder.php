<?php

namespace Database\Seeders\tests;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VerifiedUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate([
            'role_id' => '2',
            'name' => Str::random(2).'_'.Str::random(8),
            'email' => Str::random(10).'@test.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => null,
        ]);
    }
}
