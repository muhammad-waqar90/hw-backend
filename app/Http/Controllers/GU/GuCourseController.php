<?php

namespace App\Http\Controllers\GU;

use App\DataObject\CoursesData;
use App\Http\Controllers\Controller;
use App\Http\Requests\GU\Course\GuGetAvailableCoursesRequest;
use App\Repositories\GU\GuCourseRepository;
use App\Repositories\VideoRepository;
use App\Transformers\GU\Course\GuCourseAvailableListTransformer;
use App\Transformers\GU\Course\GuCourseComingSoonListTransformer;
use App\Transformers\GU\GuCourseLevelTransformer;
use App\Transformers\IU\IuCoursePreviewTransformer;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class GuCourseController extends Controller
{
    private GuCourseRepository $guCourseRepository;
    private VideoRepository $videoRepository;

    public function __construct(GuCourseRepository $guCourseRepository, VideoRepository $videoRepository)
    {
        $this->guCourseRepository = $guCourseRepository;
        $this->videoRepository = $videoRepository;
    }

    public function getGuCourseAvailableList(GuGetAvailableCoursesRequest $request)
    {
        $order = $request->order ? CoursesData::AVAILABLE_COURSES_ORDER[$request->order] : CoursesData::AVAILABLE_COURSES_ORDER['createdDate'];
        $orderDirection = $request->orderDirection ? CoursesData::ORDER_DIRECTION[$request->orderDirection] : CoursesData::ORDER_DIRECTION['DESC'];

        $data = $this->guCourseRepository->getGuCourseAvailableList($request->searchText, $request->categoryId, $order, $orderDirection);

        $fractal = fractal($data->getCollection(), new GuCourseAvailableListTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getCourse($id)
    {
        try {
            $data = $this->guCourseRepository->getCoursePreview($id);
            if (!$data)
                return response()->json(['errors' => Lang::get('general.notFound')], 404);

            $data->video_preview = $data->video_preview ? $this->videoRepository->generateLinkForLesson($data->video_preview, true) : '';
            $fractal = fractal($data, new IuCoursePreviewTransformer());
            return response()->json($fractal, 200);
        } catch (\Exception $e) {
            Log::error('Exception: GuCourseController@getCourse', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
    }

    public function getCourseLevel($courseId, $value)
    {
        try {
            $data = $this->guCourseRepository->getCourseLevel($courseId, $value);

            if ($data->courseModules->count() == 0)
                return response()->json(['errors' => Lang::get('general.notFound')], 404);

            if ($data->value !== 1)
                $data->previousLevel = null;

            $fractal = fractal($data, new GuCourseLevelTransformer());

            return response()->json($fractal, 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
    }

    public function getGuCourseComingSoonList()
    {
        $order = CoursesData::COMING_SOON_COURSES_ORDER['createdDate'];
        $orderDirection = CoursesData::ORDER_DIRECTION['DESC'];
        $data = $this->guCourseRepository->getGuCourseComingSoonList($order, $orderDirection)->simplePaginate(config('course.pagination'));

        $fractal = fractal($data->getCollection(), new GuCourseComingSoonListTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }
}
