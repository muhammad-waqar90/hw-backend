<?php

namespace App\Http\Controllers\AF;

use App\Events\Courses\CourseModules\CourseModuleCreated;
use App\Events\Courses\CourseModules\CourseModuleUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Modules\AfCourseModuleCreateRequest;
use App\Http\Requests\AF\Modules\AfCourseModuleSortingRequest;
use App\Http\Requests\AF\Modules\AfCourseModuleUpdateRequest;
use App\Repositories\AF\AfCourseModuleRepository;
use App\Repositories\AF\AfCourseRepository;
use App\Repositories\AF\AfProductRepository;
use App\Traits\FileSystemsCloudTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class AfCourseModuleController extends Controller
{
    use FileSystemsCloudTrait;

    private AfCourseRepository $afCourseRepository;

    private AfCourseModuleRepository $afCourseModuleRepository;

    private AfProductRepository $afProductRepository;

    public function __construct(AfCourseRepository $afCourseRepository, AfCourseModuleRepository $afCourseModuleRepository, AfProductRepository $afProductRepository)
    {
        $this->afCourseRepository = $afCourseRepository;
        $this->afCourseModuleRepository = $afCourseModuleRepository;
        $this->afProductRepository = $afProductRepository;
    }

    public function createModule(AfCourseModuleCreateRequest $request, int $courseId, int $levelId)
    {
        $course = $this->afCourseRepository->getCourse($courseId);
        if (! $course) {
            return response()->json(['errors' => 'Course not found'], 404);
        }

        $level = $this->afCourseRepository->getCourseLevel($course->id, $levelId);
        if (! $level) {
            return response()->json(['errors' => 'Level not found'], 404);
        }

        $thumbnail = $request->img ? $this->uploadFile($this->afCourseModuleRepository->getThumbnailS3StoragePath(), $request->img) : null;

        DB::beginTransaction();
        try {
            $courseModule = $this->afCourseModuleRepository->createModule(
                $course->id,
                $level->id,
                $request->order_id,
                $request->name,
                $request->description,
                $thumbnail,
                $request->video_preview,
                $request->ebook_price,
            );

            // Can not bind a book until module has atleast one lecture with lecture notes
            // if ($request->book_id) {
            //     $this->afProductRepository->bindPhysicalBookWithCourseModule($request->book_id, $courseModule->id);
            // }

            CourseModuleCreated::dispatchIf($request->module_has_exam, $courseModule);
            DB::commit();

            return response()->json(['message' => Lang::get('general.successfullyCreated', ['model' => 'module'])], 200);
        } catch (Exception $e) {
            DB::rollback();

            Log::error('Exception: AfCourseModuleController@createModule', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function updateModule(AfCourseModuleUpdateRequest $request, int $courseId, int $levelId, int $courseModuleId)
    {
        $module = $this->afCourseModuleRepository->getModule($courseModuleId, $levelId, $courseId);
        if (! $module) {
            return response()->json(['errors' => 'Module not found'], 404);
        }

        if (! $module->has_ebook && $request->book_id) {
            return response()->json(['errors' => 'Can not bind a book. Please make sure module have atleast one lecture with lecture notes'], 403);
        }

        $thumbnail = $request->img ? $this->updateFile($this->afCourseModuleRepository->getThumbnailS3StoragePath(), $module->img, $request->img) : $module->img;

        DB::beginTransaction();
        try {
            $this->afCourseModuleRepository->updateModule(
                $courseModuleId,
                $request->order_id,
                $request->name,
                $request->description,
                $thumbnail,
                $request->video_preview,
                $request->ebook_price
            );

            $boundedBook = $this->afProductRepository->getBookBoundedWithModule([$courseModuleId])->first();
            if ($request->book_id && $request->book_id != $boundedBook?->id) {
                //unbind old book and bind new one
                if ($boundedBook) {
                    $this->afProductRepository->unbindPhysicalBookWithCourseModule([$courseModuleId]);
                }

                $this->afProductRepository->bindPhysicalBookWithCourseModule($request->book_id, $courseModuleId);
            } elseif ((! $request->book_id) && $boundedBook) {
                //unbind book
                $this->afProductRepository->unbindPhysicalBookWithCourseModule([$courseModuleId]);
            }

            CourseModuleUpdated::dispatch($courseModuleId, $request->module_has_exam);
            DB::commit();

            return response()->json(['message' => Lang::get('general.successfullyUpdated', ['model' => 'module'])], 200);
        } catch (Exception $e) {
            DB::rollback();

            Log::error('Exception: AfCourseModuleController@updateModule', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function deleteModule(int $courseId, int $levelId, $moduleIds)
    {
        $moduleIds = explode(',', $moduleIds);
        $modules = $this->afCourseModuleRepository->checkIfAllModulesExist($moduleIds, $levelId, $courseId);
        if (! $modules) {
            return response()->json(['errors' => 'Module not found'], 404);
        }

        DB::beginTransaction();
        try {
            $boundedBooks = $this->afProductRepository->getBookBoundedWithModule($moduleIds);
            if ($boundedBooks) {
                $this->afProductRepository->unbindPhysicalBookWithCourseModule($moduleIds);
            }

            $this->afCourseModuleRepository->deleteModule($moduleIds);

            $level = $this->afCourseRepository->getCourseLevel($courseId, $levelId);
            if ($level->value === 1) {
                $this->afCourseRepository->updateCourseHasLevel1Ebook($level->course_id, $level->id);
            }
            DB::commit();

            return response()->json(['message' => Lang::get('general.successfullyDeleted', ['model' => 'module(s)'])], 200);
        } catch (Exception $e) {
            DB::rollback();

            Log::error('Exception: AfCourseModuleController@deleteModule', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function sortModule(AfCourseModuleSortingRequest $request, int $courseId, int $levelId)
    {
        $modules = $request->toArray();
        $moduleIds = array_column($modules, 'id');

        $exists = $this->afCourseModuleRepository->checkIfAllModulesExist($moduleIds, $levelId, $courseId);
        if (! $exists) {
            return response()->json(['errors' => 'Module not found'], 404);
        }

        $this->afCourseModuleRepository->sortModule($modules);

        return response()->json(['message' => Lang::get('general.successfullySorted', ['model' => 'modules'])], 200);
    }
}
