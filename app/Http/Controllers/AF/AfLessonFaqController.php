<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Lessons\Faqs\AfCreateUpdateLessonFaq;
use App\Http\Requests\AF\Lessons\Faqs\AfLessonFaqListRequest;
use App\Repositories\AF\AfLessonFaqRepository;
use Illuminate\Support\Facades\Lang;

class AfLessonFaqController extends Controller
{
    private AfLessonFaqRepository $afLessonFaqRepository;

    public function __construct(AfLessonFaqRepository $afLessonFaqRepository)
    {
        $this->afLessonFaqRepository = $afLessonFaqRepository;
    }

    public function getLessonFaqList(int $lessonId, AfLessonFaqListRequest $request)
    {
        $data = $this->afLessonFaqRepository->getLessonFaqList($lessonId, $request->searchText);
        return response()->json($data, 200);
    }

    public function createLessonFaq(AfCreateUpdateLessonFaq $request)
    {
        $this->afLessonFaqRepository->createLessonFaq($request->lesson_id, $request->question, $request->answer);
        return response()->json(['message' => 'Successfully created lesson faq'], 200);
    }

    public function updateLessonFaq(AfCreateUpdateLessonFaq $request, int $id)
    {
        $faq = $this->afLessonFaqRepository->getLessonFaq($id);
        if(!$faq)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->afLessonFaqRepository->updateLessonFaq($id, $request->lesson_id, $request->question, $request->answer);
        return response()->json(['message' => 'Successfully updated lesson faq'], 200);
    }

    public function deleteLessonFaq(int $id)
    {
        $faq = $this->afLessonFaqRepository->getLessonFaq($id);
        if(!$faq)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->afLessonFaqRepository->deleteLessonFaq($id);
        return response()->json(['message' => 'Successfully deleted lesson faq'], 200);
    }
}
