<?php

namespace App\Http\Controllers\AF;

use App\DataObject\AF\CourseStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Lessons\AfLessonCreateRequest;
use App\Http\Requests\AF\Lessons\AfLessonSortingRequest;
use App\Http\Requests\AF\Lessons\AfLessonUpdateRequest;
use App\Repositories\AF\AfCourseRepository;
use App\Repositories\AF\AfCourseModuleRepository;
use App\Repositories\AF\AfLessonEbookRepository;
use App\Repositories\AF\AfLessonRepository;
use App\Repositories\AF\AfPublishLessonRepository;
use App\Traits\FileSystemsCloudTrait;
use App\Traits\UtilsTrait;
use Illuminate\Support\Facades\Lang;

class AfLessonController extends Controller
{
    use FileSystemsCloudTrait;
    use UtilsTrait;

    private AfCourseRepository $afCourseRepository;
    private AfCourseModuleRepository $afCourseModuleRepository;
    private AfLessonRepository $afLessonRepository;
    private AfPublishLessonRepository $afPublishLessonRepository;
    private AfLessonEbookRepository $afLessonEbookRepository;

    public function __construct(
        AfCourseRepository $afCourseRepository,
        AfCourseModuleRepository $afCourseModuleRepository,
        AfLessonRepository $afLessonRepository,
        AfPublishLessonRepository $afPublishLessonRepository,
        AfLessonEbookRepository $afLessonEbookRepository
    ) {
        $this->afCourseRepository = $afCourseRepository;
        $this->afCourseModuleRepository = $afCourseModuleRepository;
        $this->afLessonRepository = $afLessonRepository;
        $this->afPublishLessonRepository = $afPublishLessonRepository;
        $this->afLessonEbookRepository = $afLessonEbookRepository;
    }

    public function createLesson(AfLessonCreateRequest $request, int $courseId, int $levelId, int $courseModuleId)
    {
        $course = $this->afCourseRepository->getCourse($courseId);
        if(!$course)
            return response()->json(['errors' => 'Course not found'], 404);

        $module = $this->afCourseModuleRepository->getModule($courseModuleId, $levelId, $courseId);
        if(!$module)
            return response()->json(['errors' => 'Module not found'], 404);

        if($request->published && $this->afLessonRepository->getLastLessonByModuleId($module->id) && !$this->afLessonRepository->getLastLessonByModuleId($module->id)->published)
            return response()->json(['errors' => 'Can not add lesson with Published status after lesson with Un-Published status'], 403);

        $thumbnail = $request->img ? $this->uploadFile($this->afLessonRepository->getThumbnailS3StoragePath(), $request->img) : null;
        $lesson = $this->afLessonRepository->createLesson(
            $course->id,
            $module->id,
            $request->order_id,
            $request->name,
            $thumbnail,
            $request->description,
            $request->video,
            $request->published
        );

        if(!$request->published)
            $this->afPublishLessonRepository->updateOrCreatePublishLesson($lesson->id, $request->publish_at);

        return response()->json(['message' => Lang::get('general.successfullyCreated', ['model' => 'lesson'])], 200);
    }

    public function updateLesson(AfLessonUpdateRequest $request, int $courseId, int $levelId, int $courseModuleId, int $lessonId)
    {
        $lesson = $this->afLessonRepository->getLesson($courseId, $courseModuleId, $lessonId);
        if(!$lesson)
            return response()->json(['errors' => 'Lesson not found'], 404);

        if($request->published != $lesson->published && !$this->afLessonRepository->checkIfLessonUpdateValid($courseModuleId, $lesson->published ? $lesson->order_id + 1 : $lesson->order_id - 1, $lesson->published))
            return response()->json(['errors' => 'Can not update lesson. Lessons status should be in sequence of Published to Unpublished.'], 403);

        $course = $this->afCourseRepository->getCourse($courseId);

        // cannot un-published if CourseStatus not DRAFT or COMING_SOON
        if($lesson->published && !$request->published && !$this->existInArray($course->status, [CourseStatusData::DRAFT, CourseStatusData::COMING_SOON]))
            return response()->json(['errors' => 'Lesson cannot be un-published as course status is not DRAFT or COMING SOON'], 403);

        $thumbnail = $request->img ? $this->updateFile($this->afLessonRepository->getThumbnailS3StoragePath(), $lesson->img, $request->img) : $lesson->img;
        $this->afLessonRepository->updateLesson(
            $lesson->id,
            $request->order_id,
            $request->name,
            $thumbnail,
            $request->description,
            $request->video,
            $request->published
        );

        $request->published ?
            $this->afPublishLessonRepository->removePublishLesson($lesson->id) :
            $this->afPublishLessonRepository->updateOrCreatePublishLesson($lesson->id, $request->publish_at);

        return response()->json(['message' => Lang::get('general.successfullyUpdated', ['model' => 'lesson'])], 200);
    }

    public function deleteLesson(int $courseId, int $levelId, int $courseModuleId, $lessonIds)
    {
        $lessonIds = explode(',', $lessonIds);
        $lessons = $this->afLessonRepository->checkIfAllLessonsExist($courseId, $courseModuleId, $lessonIds);
        if(!$lessons)
            return response()->json(['errors' => 'Lesson not found'], 404);

        $this->afLessonRepository->deleteLesson($lessonIds);

        $this->updateModuleHasEbook($courseModuleId);

        $level = $this->afCourseRepository->getCourseLevel($courseId, $levelId);
        if ($level->value === 1) $this->afCourseRepository->updateCourseHasLevel1Ebook($level->course_id, $level->id);

        return response()->json(['message' => Lang::get('general.successfullyDeleted', ['model' => 'lessons'])], 200);
    }

    public function sortLesson(AfLessonSortingRequest $request, int $courseId, int $levelId, int $courseModuleId)
    {
        $lessons = $request->toArray();
        $lessonIds = array_column($lessons, 'id');

        $exists = $this->afLessonRepository->checkIfAllLessonsExist($courseId, $courseModuleId, $lessonIds);
        if(!$exists)
            return response()->json(['errors' => 'Lesson not found'], 404);

        if(!$this->afLessonRepository->checkIfLessonSortValid($lessons))
            return response()->json(['errors' => 'Can not sort lessons. Lessons status should be in sequence of Published to Unpublished.'], 403);

        $this->afLessonRepository->sortLesson($lessons);
        return response()->json(['message' => Lang::get('general.successfullySorted', ['model' => 'lessons'])], 200);
    }

    private function updateModuleHasEbook($courseModuleId)
    {
        $allLessonIds = $this->afLessonRepository->getAllLessonByModuleId($courseModuleId)->pluck('id');
        $hasEbook = $this->afLessonEbookRepository->checkIfAnyLessonHasEbook($allLessonIds->toArray());

        return $this->afCourseModuleRepository->updateModuleHasEbook($courseModuleId, $hasEbook);
    }
}
