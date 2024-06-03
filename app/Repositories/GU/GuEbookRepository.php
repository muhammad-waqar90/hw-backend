<?php

namespace App\Repositories\GU;

use App\Models\Course;

class GuEbookRepository
{
    private Course $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function getEbookListPerLevel($courseId)
    {
        return $this->course
            ->select('id', 'name', 'img', 'price')
            ->where('id', $courseId)
            ->with('courseLevel', function ($query) {
                $query->where('value', 1)
                    ->with('courseModules', function ($query) {
                        $query->select('course_modules.*')
                            ->where('has_ebook', true);
                    });
            })
            ->first();
    }
}
