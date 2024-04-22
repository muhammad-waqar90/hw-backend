<?php

namespace App\Repositories\IU;

use App\DataObject\AF\CourseStatusData;
use App\DataObject\CoursesData;
use App\DataObject\QuizData;
use App\DataObject\UserProgressData;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLevel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\IU\IuUserRepository;

class IuCourseRepository
{

    private Course $course;
    private CourseLevel $courseLevel;

    public function __construct(Course $course, CourseLevel $courseLevel)
    {
        $this->course = $course;
        $this->courseLevel = $courseLevel;
    }

    public function getIuCourseAvailableList($userId, $searchText = null, $categoryId = null, $order = CoursesData::AVAILABLE_COURSES_ORDER['createdDate'], $orderDirection = CoursesData::ORDER_DIRECTION['DESC'])
    {
        return $this->course->select('courses.*')
            ->whereNotIn('courses.id', IuUserRepository::courseIdsOwnedByUser($userId))
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
            ->with('tier')
            ->where('status', CourseStatusData::PUBLISHED)
            ->orderBy($order, $orderDirection);
    }

    public function getIuCourseComingSoonList($searchText = null, $categoryId = null, $order = CoursesData::COMING_SOON_COURSES_ORDER['createdDate'], $orderDirection = CoursesData::ORDER_DIRECTION['DESC'])
    {
        return $this->course
            ->select('id', 'name', 'description', 'img')
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('name', 'LIKE', "%$searchText%");
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('courses.category_id', $categoryId);
            })
            ->where('status', CourseStatusData::COMING_SOON)
            ->orderBy($order, $orderDirection);
    }

    public function getIuCourseOwnedList($userId, $searchText = null, $categoryId = null, $order = CoursesData::OWNED_COURSES_ORDER['recentlyUsed'], $orderDirection = CoursesData::ORDER_DIRECTION['DESC'])
    {
        return $this->course->select('courses.*', 'up.progress')
            ->whereIn('courses.id', IuUserRepository::courseIdsOwnedByUser($userId))
            ->leftJoin('user_progress as up', function($query) use ($userId)
            {
                $query->on('up.entity_id', '=', 'courses.id')
                    ->where('up.user_id', $userId)
                    ->where('up.entity_type', UserProgressData::ENTITY_COURSE);
            })
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('courses.name', 'LIKE', "%$searchText%");
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('courses.category_id', $categoryId);
            })
            ->when($order == CoursesData::OWNED_COURSES_ORDER['recentlyUsed'], function($query) {
                $query->addSelect('up.updated_at as progress_updated_at');
              })
            ->with('category')
            ->orderBy($order, $orderDirection);
    }

    public function getIuCoursePreview ($id)
    {
        $course = $this->course
            ->select('courses.*')
            ->where('courses.id', $id)
            ->where('status', CourseStatusData::PUBLISHED)
            ->with('tier')
            ->with('courseLevels')
            ->with('courseLevel', function ($query){
                $query->where('value', 1)
                    ->with('courseModules', function ($query) {
                        $query->select('course_modules.*')
                            ->with('lessons', function($query) {
                                $query->select('lessons.id', 'lessons.name', 'lessons.course_module_id', 'lessons.description', 'lessons.published',
                                    'lessons.order_id', 'lessons.img');
                            });
                    });
            })
            ->leftJoin('course_levels as cl', 'courses.id', '=', 'cl.course_id')
            ->with('categoryWithRecursiveParents'.Category::minimalWithData())
            ->first();
        if($course)
            $course->preview = true;

        return $course;
    }

    public function getIuCourse ($userId, $id)
    {
        return $this->course
            ->select('courses.*', 'up.progress')
            ->where('courses.id', $id)
            ->with('courseLevels')
            ->with('courseLevel', function ($query) use ($userId) {
                $query->select('course_levels.*', 'up.progress', 'qz.id as quiz_id', 'uqz.user_quiz_failed_id')
                    ->where('value', 1)
                    ->leftJoin('user_progress as up', function($query) use ($userId)
                    {
                        $query->on('up.entity_id', '=', 'course_levels.id')
                            ->where('up.user_id', $userId)
                            ->where('up.entity_type', UserProgressData::ENTITY_COURSE_LEVEL);
                    })
                    ->leftJoin('quizzes as qz', function($query)
                    {
                        $query->on('qz.entity_id', '=', 'course_levels.id')
                            ->where('qz.entity_type', QuizData::ENTITY_COURSE_LEVEL);
                    })
                    ->leftJoinSub(
                        DB::table('user_quizzes as uqz')
                            ->select('entity_id', DB::raw('MAX(id) as user_quiz_failed_id'))
                            ->where('user_id', $userId)
                            ->where('status', QuizData::STATUS_COMPLETED)
                            ->where('score', '<', QuizData::DEFAULT_PASSING_SCORE)
                            ->where('entity_type', QuizData::ENTITY_COURSE_LEVEL)
                            ->groupBy('entity_id')
                        , 'uqz', function($join) {
                        $join->on('course_levels.id', '=', 'uqz.entity_id');
                    })
                    ->with('courseModules', function ($query) use ($userId) {
                        $query->select('course_modules.*', 'up.progress', 'qz.id as quiz_id', 'uqz.user_quiz_failed_id')
                            ->with('lessons', function($query) use ($userId) {
                                $query->select('lessons.id', 'lessons.name', 'lessons.course_module_id', 'lessons.order_id',
                                        'lessons.description','lessons.img', 'lessons.published', 'up.progress', 'ln.content as userNote', 'qz.id as quiz_id', 'uqz.user_quiz_failed_id')
                                   ->leftJoin('user_progress as up', function($query) use ($userId)
                                    {
                                        $query->on('up.entity_id', '=', 'lessons.id')
                                            ->where('up.user_id', $userId)
                                            ->where('up.entity_type', UserProgressData::ENTITY_LESSON);
                                    })
                                    ->leftJoin('lesson_notes as ln', function($query) use ($userId)
                                    {
                                        $query->on('ln.lesson_id', '=', 'lessons.id')
                                            ->where('ln.user_id', $userId);
                                    })
                                    ->leftJoin('quizzes as qz', function($query)
                                    {
                                        $query->on('qz.entity_id', '=', 'lessons.id')
                                            ->where('qz.entity_type', QuizData::ENTITY_LESSON);
                                    })
                                    ->leftJoinSub(
                                        DB::table('user_quizzes as uqz')
                                            ->select('entity_id', DB::raw('MAX(id) as user_quiz_failed_id'))
                                            ->where('user_id', $userId)
                                            ->where('status', QuizData::STATUS_COMPLETED)
                                            ->where('score', '<', QuizData::DEFAULT_PASSING_SCORE)
                                            ->where('entity_type', QuizData::ENTITY_LESSON)
                                            ->groupBy('entity_id')
                                        , 'uqz', function($join) {
                                            $join->on('lessons.id', '=', 'uqz.entity_id');
                                        });

                            })
                            ->leftJoin('user_progress as up', function($query) use ($userId)
                            {
                                $query->on('up.entity_id', '=', 'course_modules.id')
                                    ->where('up.user_id', $userId)
                                    ->where('up.entity_type', UserProgressData::ENTITY_COURSE_MODULE);
                            })
                            ->leftJoin('quizzes as qz', function($query)
                            {
                                $query->on('qz.entity_id', '=', 'course_modules.id')
                                    ->where('qz.entity_type', QuizData::ENTITY_COURSE_MODULE);
                            })
                            ->leftJoinSub(
                                DB::table('user_quizzes as uqz')
                                    ->select('entity_id', DB::raw('MAX(id) as user_quiz_failed_id'))
                                    ->where('user_id', $userId)
                                    ->where('status', QuizData::STATUS_COMPLETED)
                                    ->where('score', '<', QuizData::DEFAULT_PASSING_SCORE)
                                    ->where('entity_type', QuizData::ENTITY_COURSE_MODULE)
                                    ->groupBy('entity_id')
                                , 'uqz', function($join) {
                                $join->on('course_modules.id', '=', 'uqz.entity_id');
                            });
                    });
            })
            ->join('course_levels as cl', 'courses.id', '=', 'cl.course_id')
            ->leftJoin('user_progress as up', function($query) use ($userId)
            {
                $query->on('up.entity_id', '=', 'courses.id')
                    ->where('up.user_id', $userId)
                    ->where('up.entity_type', UserProgressData::ENTITY_COURSE);
            })
            ->with('categoryWithRecursiveParents'.Category::minimalWithData())
            ->first();
    }

    public function getIuCourseLevel ($userId, $courseId, $value)
    {
        return $this->courseLevel->select('course_levels.*', 'up.progress', 'qz.id as quiz_id', 'uqz.user_quiz_failed_id')
            ->where('course_id', $courseId)
            ->where('value', $value)
            ->leftJoin('user_progress as up', function($query) use ($userId)
            {
                $query->on('up.entity_id', '=', 'course_levels.id')
                    ->where('up.user_id', $userId)
                    ->where('up.entity_type', UserProgressData::ENTITY_COURSE_LEVEL);
            })
            ->leftJoin('quizzes as qz', function($query)
            {
                $query->on('qz.entity_id', '=', 'course_levels.id')
                    ->where('qz.entity_type', QuizData::ENTITY_COURSE_LEVEL);
            })
            ->leftJoinSub(
                DB::table('user_quizzes as uqz')
                    ->select('entity_id', DB::raw('MAX(id) as user_quiz_failed_id'))
                    ->where('user_id', $userId)
                    ->where('status', QuizData::STATUS_COMPLETED)
                    ->where('score', '<', QuizData::DEFAULT_PASSING_SCORE)
                    ->where('entity_type', QuizData::ENTITY_COURSE_LEVEL)
                    ->groupBy('entity_id')
                , 'uqz', function($join) {
                $join->on('course_levels.id', '=', 'uqz.entity_id');
            })
            ->with('courseModules', function ($query) use ($userId){
                $query->select('course_modules.*', 'up.progress', 'qz.id as quiz_id', 'uqz.user_quiz_failed_id')
                    ->with('lessons', function($query) use ($userId) {
                        $query->select('lessons.id', 'lessons.name', 'lessons.course_module_id', 'lessons.order_id', 'lessons.img',
                            'ln.content as userNote', 'lessons.description', 'lessons.published', 'up.progress', 'qz.id as quiz_id', 'uqz.user_quiz_failed_id')
                            ->leftJoin('user_progress as up', function($query) use ($userId)
                            {
                                $query->on('up.entity_id', '=', 'lessons.id')
                                    ->where('up.user_id', $userId)
                                    ->where('up.entity_type', UserProgressData::ENTITY_LESSON);
                            })
                            ->leftJoin('lesson_notes as ln', function($query) use ($userId)
                            {
                                $query->on('ln.lesson_id', '=', 'lessons.id')
                                    ->where('ln.user_id', $userId);
                            })
                            ->leftJoin('quizzes as qz', function($query)
                            {
                                $query->on('qz.entity_id', '=', 'lessons.id')
                                    ->where('qz.entity_type', QuizData::ENTITY_LESSON);
                            })
                            ->leftJoinSub(
                                DB::table('user_quizzes as uqz')
                                    ->select('entity_id', DB::raw('MAX(id) as user_quiz_failed_id'))
                                    ->where('user_id', $userId)
                                    ->where('status', QuizData::STATUS_COMPLETED)
                                    ->where('score', '<', QuizData::DEFAULT_PASSING_SCORE)
                                    ->where('entity_type', QuizData::ENTITY_LESSON)
                                    ->groupBy('entity_id')
                                , 'uqz', function($join) {
                                $join->on('lessons.id', '=', 'uqz.entity_id');
                            });
                    })
                    ->leftJoin('user_progress as up', function($query) use ($userId)
                    {
                        $query->on('up.entity_id', '=', 'course_modules.id')
                            ->where('up.user_id', $userId)
                            ->where('up.entity_type', UserProgressData::ENTITY_COURSE_MODULE);
                    })
                    ->leftJoin('quizzes as qz', function($query)
                    {
                        $query->on('qz.entity_id', '=', 'course_modules.id')
                            ->where('qz.entity_type', QuizData::ENTITY_COURSE_MODULE);
                    })
                    ->leftJoinSub(
                        DB::table('user_quizzes as uqz')
                            ->select('entity_id', DB::raw('MAX(id) as user_quiz_failed_id'))
                            ->where('user_id', $userId)
                            ->where('status', QuizData::STATUS_COMPLETED)
                            ->where('score', '<', QuizData::DEFAULT_PASSING_SCORE)
                            ->where('entity_type', QuizData::ENTITY_COURSE_MODULE)
                            ->groupBy('entity_id')
                        , 'uqz', function($join) {
                        $join->on('course_modules.id', '=', 'uqz.entity_id');
                    });
            })
            ->first();
    }

    public function getIuCourseLevelProgress($userId, $courseId, $value)
    {
        return $this->courseLevel->select('course_levels.*', 'up.progress')
            ->where('course_id', $courseId)
            ->where('value', $value)
            ->leftJoin('user_progress as up', function($query) use ($userId)
            {
                $query->on('up.entity_id', '=', 'course_levels.id')
                    ->where('up.user_id', $userId)
                    ->where('up.entity_type', UserProgressData::ENTITY_COURSE_LEVEL);
            })
            ->first();
    }

    public function assignCourseToUser($userId, $courseId): bool
    {
        return DB::table('course_user')->insert([
            'user_id'       => $userId,
            'course_id'     => $courseId,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now()
        ]);
    }

    public function getFirstModuleIdOfCourse($id)
    {
        return $this->courseLevel->select('course_levels.*', 'cm.id as courseModuleId')
            ->where('course_levels.course_id', $id)
            ->where('value', 1)
            ->leftJoin('course_modules as cm', function($query) {
                $query->on('cm.course_level_id', '=', 'course_levels.id')
                    ->where('cm.order_id', 1);
            })
            ->value('courseModuleId');
    }

    // TODO: same fun for AF as well - can make it re-usable for both IU/AF
    public function getCoursesListQuery($searchText = null, $detail = false, $courseIds = null) {
        return $this->course
            ->when($searchText, function ($query, $searchText) {
                return $query->where('name', 'LIKE', "%$searchText%");
            })
            ->when($detail, function ($query) {
                return $query
                    ->with('category')
                    ->with('tier')
                    ->withCount('courseLevels');
            })
            ->when($courseIds, function ($query, $courseIds) {
                return $query->whereIn('id', $courseIds);
            });
    }

    public static function getCourseThumbnailS3StoragePath()
    {
        return 'courses/thumbnails/';
    }

    public static function getCourseModuleThumbnailS3StoragePath()
    {
        return 'courses/modules/thumbnails/';
    }

    public static function getCourseLessonThumbnailS3StoragePath()
    {
        return 'courses/modules/lessons/thumbnails/';
    }
}
