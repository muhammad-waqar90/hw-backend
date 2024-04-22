<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Models\EbookDisablePrompt;
use App\Repositories\IU\IuEbookRepository;
use App\Transformers\IU\Cart\IuCartCourseEbooksTransformer;
use App\Transformers\IU\Course\IuEbookTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuEbookController extends Controller
{
    private IuEbookRepository $iuEbookRepository;

    public function __construct(IuEbookRepository $iuEbookRepository)
    {
        $this->iuEbookRepository = $iuEbookRepository;
    }

    public function get(Request $request, $courseId, $lessonId)
    {
        $ebook = $this->iuEbookRepository->getByLessonId($lessonId);
        if (!$ebook)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $ebook->src = $this->iuEbookRepository->generateS3SignedEbook($ebook->content);

        $fractal = fractal($ebook, new IuEbookTransformer());

        return response()->json($fractal, 200);
    }

    public function dismiss(Request $request, $courseId, $lessonId)
    {
        EbookDisablePrompt::updateOrCreate(
            [
                'user_id'           => $request->user()->id,
                'course_module_id'  => $request->lesson->course_module_id
            ],
        );

        return response()->json(['message' => Lang::get('iu.ebook.successfullyDismissedEbook')], 200);
    }

    public function getEbookListPerLevel(Request $request, $courseId, $level)
    {
        $userId = $request->user()->id;
        $data = $this->iuEbookRepository->getEbookListPerLevel($courseId, $level, $userId);

        $fractal = fractal($data, new IuCartCourseEbooksTransformer());
        return response()->json($fractal, 200);
    }
}
