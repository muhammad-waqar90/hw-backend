<?php

namespace Database\Factories;

use App\DataObject\CertificateEntityData;
use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class CertificateFactory extends Factory
{
    /**
     * The name of the certificate's corresponding model.
     *
     * @var string
     */
    protected $model = Certificate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user_id = DB::table('users')->pluck('id');
        $course_modules_id = DB::table('course_modules')->pluck('id');
        return [
            'user_id'       =>  $user_id->random(),
            'entity_id'     =>  $course_modules_id->random(),
            'entity_type'   =>  CertificateEntityData::ENTITY_COURSE_MODULE,
        ];
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id'   =>  $id,
        ]);
    }

    public function withCourseLevelId($id)
    {
        return $this->state(fn () => [
            'entity_id'     =>  $id,
            'entity_type'   =>  CertificateEntityData::ENTITY_COURSE_LEVEL,
        ]);
    }

    public function withCourseModuleId($id)
    {
        return $this->state(fn () => [
            'entity_id'     =>  $id,
            'entity_type'   =>  CertificateEntityData::ENTITY_COURSE_MODULE,
        ]);
    }

    public function withCourseId($id)
    {
        return $this->state(fn () => [
            'entity_id'     =>  $id,
            'entity_type'   =>  CertificateEntityData::ENTITY_COURSE,
        ]);
    }
}
