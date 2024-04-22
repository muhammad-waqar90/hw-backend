<?php

namespace App\Repositories\AF;

use App\Models\CourseLevel;

class AfCourseLevelRepository
{
    private CourseLevel $courseLevel;

    public function __construct(CourseLevel $courseLevel)
    {
        $this->courseLevel = $courseLevel;
    }

    public function createCourseLevels($courseId, $numberOfLevels, $value = 0)
    {
        for ($level = $value + 1; $level <= $numberOfLevels; $level++) {
            $this->createCourseLevel($courseId, $level);
        }
    }

    public function createCourseLevel($courseId, $levelValue)
    {
        return $this->courseLevel->create([
            'course_id' => $courseId,
            'value' => $levelValue
        ]);
    }

    public function updateCourseLevel($name, $id)
    {
        return $this->courseLevel
            ->where('id', $id)
            ->update([
                'name' => $name
            ]);
    }

    public function getLevel($id)
    {
        return $this->courseLevel
            ->where('id', $id)
            ->first();
    }

    public function deleteLevel($courseId, $levelId)
    {
        return $this->courseLevel
            ->where('id', $levelId)
            ->where('course_id', $courseId)
            ->delete();
    }

    public function resetLevelsValue($courseId, $fromValue)
    {
        return $this->courseLevel
            ->where('course_id', $courseId)
            ->where('value', '>', $fromValue)
            ->decrement('value');
    }

    public function getCourseLevels($courseId)
    {
        return $this->courseLevel
            ->where('course_id', $courseId)
            ->orderBy('value', 'DESC')
            ->get();
    }

    public function getCourseLevelByValue($courseId, $value)
    {
        return $this->courseLevel
            ->where('course_id', $courseId)
            ->where('value', $value)
            ->first();
    }
}
