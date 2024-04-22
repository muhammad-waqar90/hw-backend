<?php

namespace App\Repositories;

use App\DataObject\QuizData;
use App\DataObject\UserProgressData;
use App\Events\Certificates\CourseCompleted;
use App\Events\Certificates\CourseLevelCompleted;
use App\Events\Certificates\CourseModuleCompleted;
use App\Models\CourseLevel;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;

class IuProgressRepository
{

    private UserProgress $userProgress;
    private Lesson $lesson;
    private CourseModule $courseModule;
    private CourseLevel $courseLevel;

    public function __construct(UserProgress $userProgress, Lesson $lesson, CourseModule $courseModule, CourseLevel $courseLevel)
    {
        $this->userProgress = $userProgress;
        $this->lesson = $lesson;
        $this->courseModule = $courseModule;
        $this->courseLevel = $courseLevel;
    }

    public function calculateLessonProgress($userId, $lessonId, $userQuiz = null)
    {
        $lesson = $this->lesson->find($lessonId);

        if(!$userQuiz)
            $progress = UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ;
        if($userQuiz)
            $progress = $userQuiz->score >= QuizData::DEFAULT_PASSING_SCORE ? UserProgressData::COMPLETED_PROGRESS : UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ;

        $this->userProgress->updateOrCreate(
                [
                    'user_id'     => $userId,
                    'entity_type' => UserProgressData::ENTITY_LESSON,
                    'entity_id'   => $lessonId
                ],
                [
                    'progress' => $progress
                ]);

        $this->calculateCourseModuleProgress($userId, $lesson->course_module_id);
    }

    public function calculateCourseModuleProgress($userId, $courseModuleId, $userQuiz = null)
    {
        $courseModule = $this->courseModule->select('course_modules.*', 'qz.id as quiz_id')
            ->where('course_modules.id', $courseModuleId)
            ->leftJoin('quizzes as qz', function($query) use ($userId)
            {
                $query->on('qz.entity_id', '=', 'course_modules.id')
                    ->where('qz.entity_type', UserProgressData::ENTITY_COURSE_MODULE);
            })
            ->first();
        $modifier = $courseModule->quiz_id ? UserProgressData::MODIFIER_FOR_ENTITY_WITH_QUIZ : 1;
        $progress = 0;

        if($userQuiz)
            $progress = $userQuiz->score >= QuizData::DEFAULT_PASSING_SCORE ? UserProgressData::COMPLETED_PROGRESS : UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ;
        if($progress == 0) {
            $moduleLessons = DB::table('lessons')->select(DB::raw('count(lessons.id) as count, sum(up.progress) as sum_progress'))
                ->where('lessons.course_module_id', $courseModuleId)
                ->leftJoin('user_progress as up', function($query) use ($userId)
                {
                    $query->on('up.entity_id', '=', 'lessons.id')
                        ->where('up.user_id', $userId)
                        ->where('up.entity_type', UserProgressData::ENTITY_LESSON);
                })
                ->first();
            $progress = (int) ceil($moduleLessons->sum_progress / $moduleLessons->count * $modifier);
        }

        $this->userProgress->updateOrCreate(
                [
                    'user_id'     => $userId,
                    'entity_type' => UserProgressData::ENTITY_COURSE_MODULE,
                    'entity_id'   => $courseModuleId
                ],
                [
                    'progress' => $progress
                ]);

        if($progress === UserProgressData::COMPLETED_PROGRESS)
            CourseModuleCompleted::dispatch($userId, $courseModuleId);

        $this->calculateCourseLevelProgress($userId, $courseModule->course_level_id);
    }

    public function calculateCourseLevelProgress($userId, $courseLevelId, $userQuiz = null)
    {
        $courseLevel = $this->courseLevel->select('course_levels.*', 'qz.id as quiz_id')
            ->where('course_levels.id', $courseLevelId)
            ->leftJoin('quizzes as qz', function($query) use ($userId)
            {
                $query->on('qz.entity_id', '=', 'course_levels.id')
                    ->where('qz.entity_type', UserProgressData::ENTITY_COURSE_LEVEL);
            })
            ->first();
        $progress = 0;
        $modifier = $courseLevel->quiz_id ? UserProgressData::MODIFIER_FOR_ENTITY_WITH_QUIZ : 1;

        if($userQuiz)
            $progress = $userQuiz->score >= QuizData::DEFAULT_PASSING_SCORE ? UserProgressData::COMPLETED_PROGRESS : UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ;
        if($progress == 0) {
            $courseLevelModules = DB::table('course_modules')->selectRaw('count(course_modules.id) as count, sum(up.progress) as sum_progress')
                ->where('course_modules.course_level_id', $courseLevelId)
                ->leftJoin('user_progress as up', function($query) use ($userId)
                {
                    $query->on('up.entity_id', '=', 'course_modules.id')
                        ->where('up.user_id', $userId)
                        ->where('up.entity_type', UserProgressData::ENTITY_COURSE_MODULE);
                })
                ->first();
            $progress = (int) ceil($courseLevelModules->sum_progress / $courseLevelModules->count * $modifier);
        }

        $this->userProgress->updateOrCreate(
                [
                    'user_id'     => $userId,
                    'entity_type' => UserProgressData::ENTITY_COURSE_LEVEL,
                    'entity_id'   => $courseLevelId
                ],
                [
                    'progress' => $progress
                ]);

        if($progress === UserProgressData::COMPLETED_PROGRESS)
            CourseLevelCompleted::dispatch($userId, $courseLevelId);

        $this->calculateCourseProgress($userId, $courseLevel->course_id);
    }

    public function calculateCourseProgress($userId, $courseId)
    {
        $courseLevels = DB::table('course_levels')->selectRaw('count(course_levels.id) as count, sum(up.progress) as sum_progress')
            ->where('course_levels.course_id', $courseId)
            ->leftJoin('user_progress as up', function($query) use ($userId)
            {
                $query->on('up.entity_id', '=', 'course_levels.id')
                    ->where('up.user_id', $userId)
                    ->where('up.entity_type', UserProgressData::ENTITY_COURSE_LEVEL);
            })
            ->first();
        $progress = (int) ceil($courseLevels->sum_progress / $courseLevels->count );

        $this->userProgress->updateOrCreate(
                [
                    'user_id'     => $userId,
                    'entity_type' => UserProgressData::ENTITY_COURSE,
                    'entity_id'   => $courseId
                ],
                [
                'progress' => $progress
            ]);

        if($progress === UserProgressData::COMPLETED_PROGRESS)
            CourseCompleted::dispatch($userId, $courseId);
    }

}
