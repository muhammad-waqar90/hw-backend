<?php

namespace Database\Seeders\tests;

use Illuminate\Database\Seeder;

class CourseTestSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CourseUserSeeder::class,
        ]);
    }
}
