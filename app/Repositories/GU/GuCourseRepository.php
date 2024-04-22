<?php

namespace App\Repositories\GU;

use App\DataObject\AF\CourseStatusData;
use App\DataObject\CoursesData;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLevel;
use Illuminate\Support\Facades\DB;

class GuCourseRepository
{

    private Course $course;
    private CourseLevel $courseLevel;

    public function __construct(Course $course, CourseLevel $courseLevel)
    {
        $this->course = $course;
        $this->courseLevel = $courseLevel;
    }

    public function getGuCourseAvailableList($searchText = null, $categoryId = null, $order = CoursesData::AVAILABLE_COURSES_ORDER['createdDate'], $orderDirection = CoursesData::ORDER_DIRECTION['DESC'])
    {
        return $this->course->select('courses.*')
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('courses.name', 'LIKE', "%$searchText%");
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('courses.category_id', $categoryId);
            })
            ->when($order == CoursesData::AVAILABLE_COURSES_ORDER['popularity'], function($query) {
                $query->selectRaw('count(cu.course_id) as popularity')
                    ->leftJoin('course_user as cu', 'courses.id', '=', 'cu.course_id')
                    ->groupBy('courses.id');
            })
            ->with('category')
            ->where('status', CourseStatusData::PUBLISHED)
            ->orderBy($order, $orderDirection)
            ->simplePaginate(config('course.pagination'));
    }

    public function getCoursePreview($id)
    {
        $course = $this->course
            ->select(DB::raw('courses.*, max(cl.value) as max_level'))
            ->where('courses.id', $id)
            ->with('courseLevel', function ($query) {
                $query->where('value', 1)
                    ->with('courseModules', function ($query) {
                        $query->select('course_modules.*')
                            ->with('lessons', function ($query) {
                                $query->select(
                                    'lessons.id',
                                    'lessons.name',
                                    'lessons.course_module_id',
                                    'lessons.description',
                                    'lessons.order_id',
                                    'lessons.img'
                                );
                            });
                    });
            })
            ->leftJoin('course_levels as cl', 'courses.id', '=', 'cl.course_id')
            ->with('categoryWithRecursiveParents' . Category::minimalWithData())
            ->groupBy('courses.id', 'cl.course_id')
            ->first();
        if ($course)
            $course->preview = true;
        return $course;
    }

    public function getCourseLevel($courseId, $value)
    {
        return $this->courseLevel->select('course_levels.*')
            ->where('course_id', $courseId)
            ->where('value', $value)
            ->with('courseModules')
            ->first();
    }

    public function getGuCourseComingSoonList($order, $orderDirection)
    {
        return $this->course
            ->select('id', 'name', 'description', 'img')
            ->where('status', CourseStatusData::COMING_SOON)
            ->orderBy($order, $orderDirection);
    }
}
