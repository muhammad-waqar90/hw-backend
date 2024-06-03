<?php

namespace App\Repositories\AF;

use App\DataObject\AF\CourseStatusData;
use App\DataObject\UserProgressData;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Repositories\AF\AfCourseModuleRepository;
use App\Repositories\IU\IuUserRepository;
use Illuminate\Support\Facades\DB;

class AfCourseRepository
{
    private Course $course;

    private CourseLevel $courseLevel;

    private AfCourseModuleRepository $afCourseModuleRepository;

    public function __construct(
        Course $course,
        CourseLevel $courseLevel,
        AfCourseModuleRepository $afCourseModuleRepository
    ) {
        $this->course = $course;
        $this->courseLevel = $courseLevel;
        $this->afCourseModuleRepository = $afCourseModuleRepository;
    }

    public function getCoursesListQuery($searchText = null, $detail = false, $courseIds = null)
    {
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

    public function getCourse($id, $detail = false)
    {
        return $this->course
            ->where('id', $id)
            ->when($detail, function ($query) {
                return $query
                    ->with('category')
                    ->with('tier')
                    ->with('courseLevels', function ($query) {
                        $query->with('courseModules', function ($query) {
                            $query
                                ->with('quiz')
                                ->with('lessons', function ($query) {
                                    $query
                                        ->with('quiz')
                                        ->with('publishLesson');
                                })
                                ->with('book');
                        });
                    })
                    ->withCount('courseLevels');
            })
            ->first();
    }

    public function getUserEnrolledCourses($id, $searchText = null)
    {
        return $this->course->select('courses.*', 'up.progress')
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('courses.name', 'LIKE', "%$searchText%");
            })
            ->whereIn('courses.id', IuUserRepository::courseIdsOwnedByUser($id))
            ->leftJoin('user_progress as up', function ($query) use ($id) {
                $query->on('up.entity_id', '=', 'courses.id')
                    ->where('up.user_id', $id)
                    ->where('up.entity_type', UserProgressData::ENTITY_COURSE);
            })
            ->paginate(config('course.pagination'));
    }

    public function createCourse(
        $categoryId,
        $name,
        $description,
        $thumbnail,
        $price,
        $tierId,
        $videoPreview = null,
    ) {
        return $this->course->create([
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'img' => $thumbnail,
            'video_preview' => $videoPreview,
            'price' => $price,
            'tier_id' => $tierId,
            'status' => CourseStatusData::DRAFT,
        ]);
    }

    public function updateCourse(
        $id,
        $categoryId,
        $name,
        $description,
        $thumbnail,
        $price,
        $tierId,
        $videoPreview = null,
    ) {
        return $this->course
            ->where('id', $id)
            ->update([
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'img' => $thumbnail,
                'video_preview' => $videoPreview,
                'price' => $price,
                'tier_id' => $tierId,
            ]);
    }

    public function getCourseLevel($courseId, $levelId)
    {
        return $this->courseLevel
            ->where('id', $levelId)
            ->where('course_id', $courseId)
            ->first();
    }

    public function courseHasUsersEnrolled($courseId)
    {
        return DB::table('course_user')->where('course_id', $courseId)->count();
    }

    public function deleteCourse($id)
    {
        return $this->course->where('id', $id)->delete();
    }

    public function revokeCourseAccessFromUser($userId, $courseId)
    {
        return DB::table('course_user')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->delete();
    }

    public static function getThumbnailS3StoragePath()
    {
        return 'courses/thumbnails/';
    }

    public function updateCourseHasLevel1Ebook($courseId, $levelId)
    {
        $hasEbook = $this->afCourseModuleRepository->checkIfAnyModuleHasEbook($levelId);

        return $this->course
            ->where('id', $courseId)
            ->update([
                'has_level_1_ebook' => $hasEbook,
            ]);
    }

    public function updateCourseDiscountStatus($course, $isDiscounted)
    {
        $course->is_discounted = $isDiscounted;
        $course->save();
    }
}
