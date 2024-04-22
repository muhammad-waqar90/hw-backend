<?php

namespace App\Http\Controllers\AF;

use App\DataObject\AF\CourseStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Courses\AfCourseCreateRequest;
use App\Http\Requests\AF\Courses\AfCourseDiscountStatusUpdateRequest;
use App\Http\Requests\AF\Courses\AfCourseListRequest;
use App\Http\Requests\AF\Courses\AfCourseUpdateRequest;
use App\Repositories\AF\AfCourseLevelRepository;
use App\Repositories\AF\AfCourseModuleRepository;
use App\Repositories\AF\AfCourseRepository;
use App\Repositories\AF\AfLessonRepository;
use App\Traits\FileSystemsCloudTrait;
use App\Transformers\AF\AfCourseListTransformer;
use App\Transformers\AF\AfCourseTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class AfCourseController extends Controller
{
    use FileSystemsCloudTrait;

    private AfCourseRepository $afCourseRepository;
    private AfCourseLevelRepository $afCourseLevelRepository;
    private AfCourseModuleRepository $afCourseModuleRepository;
    private AfLessonRepository $afLessonRepository;

    public function __construct(
        AfCourseRepository $afCourseRepository,
        AfCourseLevelRepository $afCourseLevelRepository,
        AfCourseModuleRepository $afCourseModuleRepository,
        AfLessonRepository $afLessonRepository
    ) {
        $this->afCourseRepository = $afCourseRepository;
        $this->afCourseLevelRepository = $afCourseLevelRepository;
        $this->afCourseModuleRepository = $afCourseModuleRepository;
        $this->afLessonRepository = $afLessonRepository;
    }

    public function getCoursesList(Request $request)
    {
        $data = $this->afCourseRepository
            ->getCoursesListQuery((string) $request->query('searchText'))
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->get();

        return response()->json($data, 200);
    }

    public function getCourse(int $id)
    {
        $course = $this->afCourseRepository->getCourse($id);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        return response()->json($course, 200);
    }

    public function getCoursesListDetailed(AfCourseListRequest $request)
    {
        $courses = $this->afCourseRepository
            ->getCoursesListQuery((string) $request->query('searchText'), true)
            ->paginate(20);

        $fractal = fractal($courses->getCollection(), new AfCourseListTransformer);
        $courses->setCollection(collect($fractal));
        return response()->json($courses, 200);
    }

    public function createCourse(AfCourseCreateRequest $request)
    {
        $thumbnail = $this->uploadFile($this->afCourseRepository->getThumbnailS3StoragePath(), $request->img);
        $course = $this->afCourseRepository->createCourse(
            $request->category_id,
            $request->name,
            $request->description,
            $thumbnail,
            $request->price,
            $request->tier_id,
            $request->video_preview
        );

        $this->afCourseLevelRepository->createCourseLevels(
            $course->id,
            (int) $request->number_of_levels
        );

        return response()->json([
            'message' => Lang::get('general.successfullyCreated', ['model' => 'course']),
            'course_id' => $course->id,
        ], 200);
    }

    public function getCourseDetailed(int $id)
    {
        $course = $this->afCourseRepository->getCourse($id, true);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $data = (object) [
            'course' => fractal($course, new AfCourseTransformer()),
            'courseHasUsersEnrolled' => $this->afCourseRepository->courseHasUsersEnrolled($id)
        ];

        return response()->json($data, 200);
    }

    public function updateCourse(AfCourseUpdateRequest $request, int $id)
    {
        $course = $this->afCourseRepository->getCourse($id, true);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $thumbnail = $request->img ? $this->updateFile($this->afCourseRepository->getThumbnailS3StoragePath(), $course->img, $request->img) : $course->img;

        $this->afCourseRepository->updateCourse(
            $id,
            $request->category_id,
            $request->name,
            $request->description,
            $thumbnail,
            $request->price,
            $request->tier_id,
            $request->video_preview
        );

        return response()->json(['message' => Lang::get('general.successfullyUpdated', ['model' => 'course'])], 200);
    }

    public function deleteCourse(int $id)
    {
        $course = $this->afCourseRepository->getCourse($id);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->afCourseRepository->deleteCourse($id);

        return response()->json(['message' => Lang::get('general.successfullyDeleted', ['model' => 'course'])], 200);
    }

    public function validateCourse(int $id)
    {
        $course = $this->afCourseRepository->getCourse($id);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $lessonsHaveNoQuiz = $this->afLessonRepository->getLessonsHaveNoQuiz($id);

        return response()->json(['lessons_have_no_quiz' => $lessonsHaveNoQuiz], 200);
    }

    public function publishCourse(int $id)
    {
        $course = $this->afCourseRepository->getCourse($id, true);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        if ($course->status === CourseStatusData::PUBLISHED)
            return response()->json(['errors' => 'Course already published'], 403);

        if (count($course->courseLevels->toArray()) < 1)
            return response()->json(['errors' => 'Course can not be published. Please make sure you have atleast one level'], 403);

        if (!$this->courseContainsLesson($course->courseLevels->toArray()))
            return response()->json(['errors' => 'Course can not be published. Please make sure all levels have modules/lessons'], 403);

        $course->status = CourseStatusData::PUBLISHED;
        $course->save();

        return response()->json(['message' => 'Course successfully published'], 200);
    }

    public function unpublishCourse(int $id)
    {
        $course = $this->afCourseRepository->getCourse($id, true);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        if ($course->status === CourseStatusData::UNPUBLISHED)
            return response()->json(['errors' => 'Course already unpublished'], 403);

        if (count($course->courseLevels->toArray()) < 1)
            return response()->json(['errors' => 'Course can not be unpublished. Please make sure you have atleast one level'], 403);

        if (!$this->courseContainsLesson($course->courseLevels->toArray()))
            return response()->json(['errors' => 'Course can not be unpublished. Please make sure all levels have modules/lessons'], 403);

        $course->status = CourseStatusData::UNPUBLISHED;
        $course->save();

        return response()->json(['message' => 'Course successfully unpublished'], 200);
    }

    public function draftCourse(int $id)
    {
        $course = $this->afCourseRepository->getCourse($id);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        if ($course->status === CourseStatusData::DRAFT)
            return response()->json(['errors' => 'Course already in draft'], 403);

        $courseHasUsersEnrolled = $this->afCourseRepository->courseHasUsersEnrolled($id);
        if ($courseHasUsersEnrolled)
            return response()->json(['errors' => 'Course cannot be put in draft because it already has one or more enrolled users'], 403);

        $course->status = CourseStatusData::DRAFT;
        $course->save();

        return response()->json(['message' => 'Course status successfully changed to draft'], 200);
    }

    public function markCourseAsComingSoon(int $id)
    {
        $course = $this->afCourseRepository->getCourse($id);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        if ($course->status === CourseStatusData::COMING_SOON)
            return response()->json(['errors' => 'Course already in coming soon'], 403);

        $courseHasUsersEnrolled = $this->afCourseRepository->courseHasUsersEnrolled($id);
        if ($courseHasUsersEnrolled)
            return response()->json(['errors' => 'Course cannot be put in coming soon because it already has one or more enrolled users'], 403);

        $course->status = CourseStatusData::COMING_SOON;
        $course->save();

        return response()->json(['message' => 'Course status successfully changed to coming soon'], 200);
    }

    public function courseContainsLesson($levels)
    {
        $courseLevelModules = array_column($levels, 'course_modules');
        if (in_array([], $courseLevelModules, true))
            return false;

        return $this->modulesContainLesson($courseLevelModules);
    }

    public function modulesContainLesson($courseLevelModules, $index = 0)
    {
        $modules = $courseLevelModules[$index];

        foreach ($modules as $module) {
            if (empty($module['lessons'])) return false;
        }

        $index = $index + 1;
        if ($index >= count($courseLevelModules)) return true;

        return $this->modulesContainLesson($courseLevelModules, $index);
    }

    public function updateDiscountStatus(AfCourseDiscountStatusUpdateRequest $request)
    {
        $course = $this->afCourseRepository->getCourse($request->course_id);
        if (!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->afCourseRepository->updateCourseDiscountStatus($course, $request->is_discounted);
        return response()->json(['message' => 'Course discount status successfully updated'], 200);
    }
}
