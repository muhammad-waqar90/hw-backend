<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Http\Requests\IU\Lesson\OngoingLessonsRequest;
use App\Http\Requests\IU\Lesson\UpdateLessonNoteRequest;
use App\Http\Requests\IU\Lesson\UpdateVideoProgress;
use App\Models\Ebook;
use App\Models\EbookAccess;
use App\Models\EbookDisablePrompt;
use App\Repositories\IU\IuCourseRepository;
use App\Repositories\LessonRepository;
use App\Repositories\VideoRepository;
use App\Transformers\IU\CourseHierarchy\IuLessonHierarchyTransformer;
use App\Transformers\IU\IuLessonViewTransformer;
use App\Transformers\IU\IuModuleLessonsListTransformer;
use App\Transformers\IU\IuOngoingLessonTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class IuLessonController extends Controller
{
    private LessonRepository $lessonRepository;
    private VideoRepository $videoRepository;
    private IuCourseRepository $iuCourseRepository;

    public function __construct(LessonRepository $lessonRepository, VideoRepository $videoRepository, IuCourseRepository $iuCourseRepository)
    {
        $this->lessonRepository = $lessonRepository;
        $this->videoRepository = $videoRepository;
        $this->iuCourseRepository = $iuCourseRepository;
    }

    public function get(Request $request, int $courseId, int $lessonId)
    {
        //The lesson was already loaded in middleware so we can reuse it from inside the request
        $userId = $request->user()->id;
        $data = $request->lesson;

        $data->video = $this->videoRepository->generateLinkForLesson($data->video, true);
        $data->has_purchased_ebook = EbookAccess::where('user_id', $request->user()->id)
            ->where('course_module_id', $data->course_module_id)
            ->exists();
        $data->has_ebook = Ebook::where('lesson_id', $lessonId)->exists();
        $data->disable_ebook_prompt = EbookDisablePrompt::where('course_module_id', $data->course_module_id)
            ->where('user_id', $userId)
            ->exists();

        $data = (object) [
            'lesson' => fractal($data, new IuLessonViewTransformer()),
            'hierarchy' => fractal($data, new IuLessonHierarchyTransformer()),
        ];

        return response()->json($data, 200);
    }

    public function getLessonNote(Request $request, $courseId, $lessonId)
    {
        $data = $this->lessonRepository->getLessonNote($request->user()->id, $lessonId);
        return response()->json($data, 200);
    }

    public function updateLessonNote(UpdateLessonNoteRequest $request, $courseId, $lessonId)
    {
        $lessonNote = $this->lessonRepository->updateLessonNote($request->user()->id, $lessonId, $request->text ?: '');
        return response()->json([
            'message' => Lang::get('iu.successfullyUpdatedNote'),
            'notes_text' => $lessonNote->content,
            'notes_updated_at' => $lessonNote->updated_at
        ], 201);
    }

    public function updateVideoProgress(UpdateVideoProgress $request, $courseId, $lessonId)
    {
        try {
            $userId = $request->user()->id;
            $this->lessonRepository->updateVideoProgress($userId, $lessonId, $request->timestamp);
            if (!$request->updateLessonProgress)
                return response()->json(['message' => Lang::get('iu.successfullyUpdatedVideoProgress')], 201);

            $updatedProgress = $this->lessonRepository->updateLessonProgressOnVideoView($userId, $lessonId);

            return response()->json([
                'message' => Lang::get('iu.successfullyUpdatedVideoProgress'),
                'updatedLessonProgress' => $updatedProgress
            ], 201);
        } catch (Exception $e) {
            Log::error('Exception: IuLessonController@updateVideoProgress', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getOngoingLessons(OngoingLessonsRequest $request, Int $courseId)
    {
        $firstModuleId = $request->progress === '0' ? $this->iuCourseRepository->getFirstModuleIdOfCourse($courseId) : null;

        $data = $this->lessonRepository->getOngoingLessons($request->user()->id, $courseId, $firstModuleId);
        $fractal = fractal($data, new IuOngoingLessonTransformer());

        return response()->json($fractal, 200);
    }

    public function getAllLessonsOfModule(Request $request, int $courseId, int $courseModuleId)
    {
        $data = $this->lessonRepository->getAllLessonsOfModule(
            $request->user()->id,
            $courseId,
            $courseModuleId
        );

        $fractal = fractal($data, new IuModuleLessonsListTransformer($data));

        return response()->json($fractal, 200);
    }
}
