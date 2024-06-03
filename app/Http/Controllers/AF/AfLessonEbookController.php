<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Lessons\Ebooks\AfCreateUpdateLessonEbookRequest;
use App\Http\Requests\AF\Lessons\Ebooks\AfLessonEbookRequest;
use App\Repositories\AF\AfCourseModuleRepository;
use App\Repositories\AF\AfCourseRepository;
use App\Repositories\AF\AfLessonEbookRepository;
use App\Repositories\AF\AfLessonRepository;
use App\Repositories\IU\IuEbookRepository;
use App\Transformers\AF\AfLessonEbookTransformer;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class AfLessonEbookController extends Controller
{
    private AfLessonEbookRepository $afLessonEbookRepository;

    private AfLessonRepository $afLessonRepository;

    private AfCourseModuleRepository $afCourseModuleRepository;

    private AfCourseRepository $afCourseRepository;

    private IuEbookRepository $iuEbookRepository;

    public function __construct(
        AfLessonEbookRepository $afLessonEbookRepository,
        AfLessonRepository $afLessonRepository,
        AfCourseModuleRepository $afCourseModuleRepository,
        AfCourseRepository $afCourseRepository,
        IuEbookRepository $iuEbookRepository
    ) {
        $this->afLessonEbookRepository = $afLessonEbookRepository;
        $this->afLessonRepository = $afLessonRepository;
        $this->afCourseModuleRepository = $afCourseModuleRepository;
        $this->afCourseRepository = $afCourseRepository;
        $this->iuEbookRepository = $iuEbookRepository;
    }

    public function getLessonEbook(AfLessonEbookRequest $request, int $courseId, int $levelId, int $courseModuleId, int $lessonId)
    {
        $ebook = $this->afLessonEbookRepository->getLessonEbook($lessonId);
        if ($ebook && $request->with_src) {
            $ebook->src = $this->iuEbookRepository->generateS3SignedEbook($ebook->content);
        }

        $ebook = (object) [
            'ebook' => fractal($ebook, new AfLessonEbookTransformer()),
            'price' => $this->getEbooksPrice($lessonId),
        ];

        return response()->json($ebook, 200);
    }

    public function createLessonEbook(AfCreateUpdateLessonEbookRequest $request, int $courseId, int $levelId, int $courseModuleId, int $lessonId)
    {
        try {
            $ebook = $this->afLessonEbookRepository->getLessonEbook($lessonId);
            if ($ebook) {
                return response()->json(['errors' => 'Lecture e-notes already uploaded'], 400);
            }

            $this->afLessonEbookRepository->createLessonEbook($lessonId, $request->content);

            $this->updateModuleHasEbook($lessonId);
            $this->updateCourseHasLevel1Ebook($lessonId);

            return response()->json(['message' => 'Successfully uploaded lecture e-notes'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfLessonEbookController@createLessonEbook', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function updateLessonEbook(AfCreateUpdateLessonEbookRequest $request, int $courseId, int $levelId, int $courseModuleId, int $lessonId, int $ebookId)
    {
        try {
            $ebook = $this->afLessonEbookRepository->getLessonEbookById($ebookId);
            if (! $ebook) {
                return response()->json(['errors' => Lang::get('general.notFound')], 404);
            }

            $this->afLessonEbookRepository->updateLessonEbook($ebookId, $lessonId, $request->content);

            return response()->json(['message' => 'Successfully updated lecture e-notes'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfLessonEbookController@updateLessonEbook', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function deleteLessonEbook(int $courseId, int $levelId, int $courseModuleId, int $lessonId, int $ebookId)
    {
        try {
            $ebook = $this->afLessonEbookRepository->getLessonEbookById($ebookId);
            if (! $ebook) {
                return response()->json(['errors' => Lang::get('general.notFound')], 404);
            }

            $this->afLessonEbookRepository->deleteLessonEbook($ebookId);

            $this->updateModuleHasEbook($lessonId);
            $this->updateCourseHasLevel1Ebook($lessonId);

            return response()->json(['message' => 'Successfully deleted lecture e-notes'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfLessonEbookController@deleteLessonEbook', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    private function updateModuleHasEbook($lessonId)
    {
        $lesson = $this->afLessonRepository->getLessonById($lessonId);
        $allLessonIds = $this->afLessonRepository->getAllLessonByModuleId($lesson->course_module_id)->pluck('id');
        $hasEbook = $this->afLessonEbookRepository->checkIfAnyLessonHasEbook($allLessonIds->toArray());

        return $this->afCourseModuleRepository->updateModuleHasEbook($lesson->course_module_id, $hasEbook);
    }

    private function getEbooksPrice($lessonId)
    {
        $lesson = $this->afLessonRepository->getLessonById($lessonId);

        return $this->afCourseModuleRepository->getModuleById($lesson->course_module_id)->ebook_price;
    }

    private function updateCourseHasLevel1Ebook($lessonId)
    {
        $lesson = $this->afLessonRepository->getLessonById($lessonId, true);

        $level = $lesson->courseModule->courseLevel;
        if ($level->value !== 1) {
            return;
        }

        return $this->afCourseRepository->updateCourseHasLevel1Ebook($level->course_id, $level->id);
    }
}
