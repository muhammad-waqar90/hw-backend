<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Levels\AfCourseLevelUpdateRequest;
use App\Repositories\AF\AfCourseLevelRepository;
use App\Repositories\AF\AfCourseRepository;
use Illuminate\Support\Facades\Lang;

class AfCourseLevelController extends Controller
{
    private AfCourseRepository $afCourseRepository;
    private AfCourseLevelRepository $afCourseLevelRepository;

    public function __construct(AfCourseRepository $afCourseRepository, AfCourseLevelRepository $afCourseLevelRepository)
    {
        $this->afCourseRepository = $afCourseRepository;
        $this->afCourseLevelRepository = $afCourseLevelRepository;
    }

    public function createLevel(int $courseId)
    {
        $course = $this->afCourseRepository->getCourse($courseId);
        if(!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $courseLevels = $this->afCourseLevelRepository->getCourseLevels($courseId);
        // compute value for new level
        $levelValue = count($courseLevels) > 0 ? ($courseLevels[0]->value + 1) : 1;
        $this->afCourseLevelRepository->createCourseLevel($courseId, $levelValue);

        return response()->json(['message' => Lang::get('general.successfullyCreated', ['model' => 'level'])], 200);
    }

    public function updateLevel(AfCourseLevelUpdateRequest $request, int $courseId, int $levelId)
    {
        $course = $this->afCourseRepository->getCourse($courseId);
        if(!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $level = $this->afCourseLevelRepository->getLevel($levelId);
        if(!$level)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->afCourseLevelRepository->updateCourseLevel($request->name, $levelId);
        return response()->json(['message' => Lang::get('general.successfullyUpdated', ['model' => 'level'])], 200);
    }

    public function deleteLevel(int $courseId, int $levelId)
    {
        $course = $this->afCourseRepository->getCourse($courseId);
        if(!$course)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $level = $this->afCourseLevelRepository->getLevel($levelId);
        if(!$level)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->afCourseLevelRepository->deleteLevel($courseId, $levelId);

        // reset next level's value from the deleted one
        $this->afCourseLevelRepository->resetLevelsValue($courseId, $level->value);

        if ($level->value === 1) {
            $level = $this->afCourseLevelRepository->getCourseLevelByValue($courseId, $level->value);
            if($level) $this->afCourseRepository->updateCourseHasLevel1Ebook($courseId, $level->id);
        }

        return response()->json(['message' => Lang::get('general.successfullyDeleted', ['model' => 'level'])], 200);
    }
}
