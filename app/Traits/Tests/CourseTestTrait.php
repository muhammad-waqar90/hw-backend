<?php

namespace App\Traits\Tests;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizItem;
use App\Models\Certificate;
use App\Models\UserProgress;

use Illuminate\Support\Facades\DB;

trait CourseTestTrait
{
    public function CategoryCourseCourseModuleLessonSeeder($user = null)
    {

        //Categories (5 categories)
        $category = Category::factory()->create();
        $childCategory = Category::factory(2)->childOf($category)->create();
        Category::factory(2)->childOf($childCategory->first())->create();

        //Course (JUST ONE)
        $course = Course::factory()->withId($category->id)->create();

        //Course Levels (3 levels)
        $courseLevel = CourseLevel::factory()->withCourseId($course->id)->withValue(1)->create();
        $courseLevel2 = CourseLevel::factory()->withCourseId($course->id)->withValue(2)->create();
        $courseLevel3 = CourseLevel::factory()->withCourseId($course->id)->withValue(3)->create();

        //Course Modules (3 modules / One on each)
        $courseModule = CourseModule::factory()->withCourseLevelId($courseLevel->id)->create();
        $courseModule2 = CourseModule::factory()->withCourseLevelId($courseLevel2->id)->create();
        $courseModule3 = CourseModule::factory()->withCourseLevelId($courseLevel3->id)->create();
        /*
        /
        /   Future version of factory
        /   Must investigate recursion further
        /   Let this sleep here for a while
        /
        /   $courseModule = CourseModule::factory()->withCourseLevelId($courseLevel->id)->state(new Sequence(['order_id'  =>  '1'],['order_id'  =>  '2'],['order_id'  =>  '3']))->create();
        */ ///////////////////

        //Lessons (2 on each module)
        $lesson = Lesson::factory()->withCourseId($course->id)->withCourseModuleId($courseModule->id)->withOrder(1)->create();
        $lesson2 = Lesson::factory()->withCourseId($course->id)->withCourseModuleId($courseModule->id)->withOrder(2)->create();

        Lesson::factory()->withCourseId($course->id)->withCourseModuleId($courseModule2->id)->withOrder(1)->create();
        Lesson::factory()->withCourseId($course->id)->withCourseModuleId($courseModule2->id)->withOrder(2)->create();

        Lesson::factory()->withCourseId($course->id)->withCourseModuleId($courseModule3->id)->withOrder(1)->create();
        Lesson::factory()->withCourseId($course->id)->withCourseModuleId($courseModule3->id)->withOrder(2)->create();

        //Quizzes
        $quizzes = new \stdClass();
        $quizzes->lesson1 = Quiz::factory()->entityLessonWithId($lesson->id)->create();
        $quizzes->lesson2 = Quiz::factory()->entityLessonWithId($lesson2->id)->create();
        $quizzes->courseModule = Quiz::factory()->entityCourseModuleWithId($courseModule->id)->create();
        $quizzes->courseLevel = Quiz::factory()->entityCourseLevelWithId($courseLevel->id)->create();
        $quizzes->course = Quiz::factory()->entityCourseWithId($course->id)->create();

        //QuizItems 
        QuizItem::factory()->withQuizId($quizzes->lesson1->id)->create();
        QuizItem::factory()->withQuizId($quizzes->lesson2->id)->create();
        QuizItem::factory()->withQuizId($quizzes->courseModule->id)->create();
        QuizItem::factory()->withQuizId($quizzes->courseLevel->id)->create();
        QuizItem::factory()->withQuizId($quizzes->course->id)->create();

        //  Give user course ownership
        if ($user) {
            DB::table('course_user')->insert(
                [
                    'course_id' =>  $course->id,
                    'user_id'   =>    $user->id,
                ]
            );
        }

        //  NOT ALL DATA PACKED
        $data = new \stdClass();
        $data->category = $category;
        $data->course = $course;
        $data->courseLevel = $courseLevel;
        $data->courseModule = $courseModule;
        $data->courseModule2 = $courseModule2;
        $data->lesson = $lesson;
        $data->lesson2 = $lesson2;
        $data->quizzes = $quizzes;

        return $data;
    }

    public function GenerateCertificatesForMultipleCourses($courses, $user)
    {
        foreach ($courses as $course) {
            Certificate::factory()->withUserId($user->id)->withCourseId($course->id)->create();
        }
    }

    public function CompletedCourseSeeder($user)
    {
        $category = Category::factory()->create();
        //Course (JUST ONE)
        $course = Course::factory()->withId($category->id)->create();

        $courseLevel = CourseLevel::factory()->withCourseId($course->id)->withValue(1)->create();

        $courseModule = CourseModule::factory()->withCourseLevelId($courseLevel->id)->create();

        $lesson = Lesson::factory()->withCourseId($course->id)->withCourseModuleId($courseModule->id)->withOrder(1)->create();

        UserProgress::factory()->withUserId($user->id)->entityLessonWithId($lesson->id)->withProgress(100)->create();
        UserProgress::factory()->withUserId($user->id)->entityCourseModuleWithId($courseModule->id)->withProgress(100)->create();
        UserProgress::factory()->withUserId($user->id)->entityCourseLevelWithId($courseLevel->id)->withProgress(100)->create();
        UserProgress::factory()->withUserId($user->id)->entityCourseWithId($course->id)->withProgress(100)->create();

        DB::table('course_user')->insert(
            [
                'course_id' => $course->id,
                'user_id' => $user->id,
            ]
        );

        //  NOT ALL DATA PACKED
        $data = new \stdClass();
        $data->category = $category;
        $data->course = $course;
        $data->courseLevel = $courseLevel;
        $data->courseModule = $courseModule;
        $data->lesson = $lesson;

        return $data;
    }
}
