<?php

namespace Database\Factories;

use App\Models\EbookAccess;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class EbookAccessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $userId = DB::table('users')->pluck('id');
        $courseModuleId = DB::table('course_modules')->pluck('id');

        return [
            'user_id' => $userId->random(),
            'course_module_id' => $courseModuleId->random(),
        ];
    }

    public function withCourseModuleId($id)
    {
        return $this->state(fn () => [
            'course_module_id' => $id,
        ]);
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id' => $id,
        ]);
    }
}
