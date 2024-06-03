<?php

namespace App\Http\Controllers\IU;

use App\DataObject\CoursesData;
use App\Http\Controllers\Controller;
use App\Http\Requests\IU\IuGetAvailableCoursesRequest;
use App\Http\Requests\IU\IuGetComingSoonCoursesRequest;
use App\Http\Requests\IU\IuGetOwnCoursesRequest;
use App\Repositories\IU\IuCourseRepository;
use App\Repositories\IU\IuUserRepository;
use App\Repositories\VideoRepository;
use App\Transformers\IU\IuCourseAvailableListTransformer;
use App\Transformers\IU\IuCourseComingSoonListTransformer;
use App\Transformers\IU\IuCourseLevelTransformer;
use App\Transformers\IU\IuCourseOwnedListTransformer;
use App\Transformers\IU\IuCourseOwnedTransformer;
use App\Transformers\IU\IuCoursePreviewTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class IuCourseController extends Controller
{
    private IuCourseRepository $iuCourseRepository;

    private IuUserRepository $iuUserRepository;

    private VideoRepository $videoRepository;

    public function __construct(IuCourseRepository $iuCourseRepository, VideoRepository $videoRepository, IuUserRepository $iuUserRepository)
    {
        $this->iuCourseRepository = $iuCourseRepository;
        $this->videoRepository = $videoRepository;
        $this->iuUserRepository = $iuUserRepository;
    }

    public function getDashboard(Request $request)
    {
        $userId = $request->user()->id;

        $userOverview = $this->iuUserRepository->getUserOverview($userId)->toArray();
        $availableCourses = $this->iuCourseRepository->getIuCourseAvailableList($userId, null, null, CoursesData::AVAILABLE_COURSES_ORDER['popularity'])->limit(config('course.pagination'))->get();
        $userOwnCourses = $this->iuCourseRepository->getIuCourseOwnedList($userId)->limit(config('course.pagination'))->get();

        $availableCourses = fractal($availableCourses, new IuCourseAvailableListTransformer());

        $userOwnCourses = fractal($userOwnCourses, new IuCourseOwnedListTransformer());

        $data = ['userOverview' => $userOverview, 'availableCourses' => $availableCourses, 'userOwnCourses' => $userOwnCourses];

        return response()->json($data, 200);
    }

    public function getIuCourseAvailableList(IuGetAvailableCoursesRequest $request)
    {
        $order = $request->order ? CoursesData::AVAILABLE_COURSES_ORDER[$request->order] : CoursesData::AVAILABLE_COURSES_ORDER['createdDate'];
        $orderDirection = $request->orderDirection ? CoursesData::ORDER_DIRECTION[$request->orderDirection] : CoursesData::ORDER_DIRECTION['DESC'];

        $data = $this->iuCourseRepository->getIuCourseAvailableList(
            $request->user()->id,
            $request->searchText,
            $request->categoryId,
            $order,
            $orderDirection
        )
            ->simplePaginate(config('course.pagination'))
            ->appends([
                'order' => $request->order ?: 'createdDate',
                'orderDirection' => $request->orderDirection ?: 'DESC',
            ]);

        $fractal = fractal($data->getCollection(), new IuCourseAvailableListTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getIuCourseComingSoonList(IuGetComingSoonCoursesRequest $request)
    {
        $order = $request->order ? CoursesData::COMING_SOON_COURSES_ORDER[$request->order] : CoursesData::COMING_SOON_COURSES_ORDER['createdDate'];
        $orderDirection = $request->orderDirection ? CoursesData::ORDER_DIRECTION[$request->orderDirection] : CoursesData::ORDER_DIRECTION['DESC'];

        $data = $this->iuCourseRepository->getIuCourseComingSoonList(
            $request->searchText,
            $request->categoryId,
            $order,
            $orderDirection
        )
            ->simplePaginate(config('course.pagination'))
            ->appends([
                'order' => $request->order ?: 'createdDate',
                'orderDirection' => $request->orderDirection ?: 'DESC',
            ]);

        $fractal = fractal($data->getCollection(), new IuCourseComingSoonListTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getIuCourseOwnedList(IuGetOwnCoursesRequest $request)
    {
        $data = $this->iuCourseRepository->getIuCourseOwnedList(
            $request->user()->id,
            $request->searchText,
            $request->categoryId,
            $request->order ? CoursesData::OWNED_COURSES_ORDER[$request->order] : CoursesData::OWNED_COURSES_ORDER['recentlyUsed'],
            $request->orderDirection ? CoursesData::ORDER_DIRECTION[$request->orderDirection] : CoursesData::ORDER_DIRECTION['DESC']
        )->simplePaginate(config('course.pagination'));

        $fractal = fractal($data->getCollection(), new IuCourseOwnedListTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getIuCourse($id, Request $request)
    {
        try {
            $userId = $request->user()->id;
            $userOwnsCourse = IuUserRepository::iuUserOwnsCourse($userId, $id);

            $data = $userOwnsCourse ? $this->iuCourseRepository->getIuCourse($userId, $id) : $this->iuCourseRepository->getIuCoursePreview($id);
            if (! $data) {
                return response()->json(['errors' => Lang::get('general.notFound')], 404);
            }

            $data->video_preview = $data->video_preview ? $this->videoRepository->generateLinkForLesson($data->video_preview, true) : '';

            $fractal = fractal($data, $userOwnsCourse ? new IuCourseOwnedTransformer() : new IuCoursePreviewTransformer());

            return response()->json($fractal, 200);
        } catch (\Exception $e) {
            Log::error('Exception: IuCourseController@getIuCourse', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
    }

    public function getIuCourseLevel(Request $request, $courseId, $value)
    {
        try {
            $userId = $request->user()->id;
            $data = $this->iuCourseRepository->getIuCourseLevel($userId, $courseId, $value);

            if ($data->courseModules->count() == 0) {
                return response()->json(['errors' => Lang::get('general.notFound')], 404);
            }

            if ($data->value !== 1) {
                $data->previousLevel = $this->iuCourseRepository->getIuCourseLevelProgress($userId, $courseId, $data->value - 1);
            }

            $passedPreviousLevel = $this->computePreviousLevelPassed($data);
            $fractal = fractal($data, new IuCourseLevelTransformer($passedPreviousLevel));

            return response()->json($fractal, 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
    }

    private function computePreviousLevelPassed($data): bool
    {
        if ($data->value === 1) {
            return true;
        }
        if (! $data->previousLevel) {
            return false;
        }
        if ($data->previousLevel->progress === 100) {
            return true;
        }

        return false;
    }
}
