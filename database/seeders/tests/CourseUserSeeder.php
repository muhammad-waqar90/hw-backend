<?php

namespace Database\Seeders\tests;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users_id = DB::table('users')->pluck('id');
        $courses_id = DB::table('courses')->pluck('id');

        foreach ($users_id as $user_id) {
            DB::table('course_user')->insert(
                [
                    'course_id' => $courses_id->random(),
                    'user_id' => $user_id,
                ]
            );
        }
    }
}
