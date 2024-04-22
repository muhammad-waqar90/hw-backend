<?php

namespace App\Http\Controllers\AF;

use App\DataObject\AF\BulkImportEntityTypeData;
use App\DataObject\AF\BulkImportStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Bulk\BulkImportRequest;
use App\Http\Requests\AF\Bulk\QuizImportRequest;
use App\Jobs\AF\AfBulkImportQuizzesJob;
use App\Repositories\AF\AfBulkImportQuizzesRepository;
use App\Transformers\AF\AfBulkImportListTransformer;
use Illuminate\Support\Facades\Storage;

class AfBulkImportController extends Controller
{
    /**
     * @var AfBulkImportQuizzesRepository
     */
    private AfBulkImportQuizzesRepository $afBulkImportQuizzesRepository;

    /**
     * @param AfBulkImportQuizzesRepository $afBulkImportQuizzesRepository
     */
    public function __construct(AfBulkImportQuizzesRepository $afBulkImportQuizzesRepository)
    {
        $this->afBulkImportQuizzesRepository = $afBulkImportQuizzesRepository;
    }

    public function importCourseQuizzes(BulkImportRequest $request, $id)
    {
        $isBulkImportProcessing = $this->isBulkImportProcessing($id, BulkImportEntityTypeData::COURSE);
        if ($isBulkImportProcessing)
            return response()->json(['errors' => $isBulkImportProcessing], 400);

        $userId = $request->user()->id;

        $bis = $this->afBulkImportQuizzesRepository->initImport($userId, $id, $id, BulkImportEntityTypeData::COURSE);

        Storage::disk('s3')->putFileAs(AfBulkImportQuizzesRepository::getStoragePath($bis->id), $request->file, "$bis->id.zip");
        AfBulkImportQuizzesJob::dispatch($bis->id)->onQueue('bulk-import');

        return response()->json($bis, 200);
    }

    public function getCourseBulkImports(int $id)
    {
        $imports = $this->afBulkImportQuizzesRepository->getBulkImports($id, BulkImportEntityTypeData::COURSE);

        $fractal = fractal($imports->getCollection(), new AfBulkImportListTransformer());
        $imports->setCollection(collect($fractal));

        return response()->json($imports, 200);
    }

    public function importLessonQuizzes(QuizImportRequest $request, int $courseId, int $levelId, int $courseModuleId, int $lessonId)
    {
        $isBulkImportProcessing = $this->isBulkImportProcessing($lessonId, BulkImportEntityTypeData::LESSON);
        if ($isBulkImportProcessing)
            return response()->json(['errors' => $isBulkImportProcessing], 400);

        $userId = $request->user()->id;
        $bis = $this->afBulkImportQuizzesRepository->initImport($userId, $courseId, $lessonId, BulkImportEntityTypeData::LESSON);

        $tmpImportQuizzesDirPath = $this->afBulkImportQuizzesRepository->getTmpImportQuizzesPath($bis->id);
        $tmpImportQuizFileName = $this->afBulkImportQuizzesRepository->getTmpImportQuizFileName($lessonId, $request->file('file'));

        $this->afBulkImportQuizzesRepository->makeLessonImportIndexFile(
            $lessonId,
            $request->duration,
            $request->sample_size,
            $tmpImportQuizzesDirPath,
            $tmpImportQuizFileName
        );

        $this->afBulkImportQuizzesRepository->makeImportReady($tmpImportQuizzesDirPath, $tmpImportQuizFileName, $request->file('file'));

        Storage::disk('s3')->putFileAs(AfBulkImportQuizzesRepository::getStoragePath($bis->id), storage_path('app/' . $tmpImportQuizzesDirPath . '.zip'), "$bis->id.zip");

        $this->afBulkImportQuizzesRepository->cleanup($tmpImportQuizzesDirPath);
        AfBulkImportQuizzesJob::dispatch($bis->id)->onQueue('bulk-import');

        return response()->json(['message' => 'Import is being processed'], 200);
    }

    public function getLessonBulkImports(int $courseId, int $levelId, int $courseModuleId, int $lessonId)
    {
        $imports = $this->afBulkImportQuizzesRepository->getBulkImports($lessonId, BulkImportEntityTypeData::LESSON);

        $fractal = fractal($imports->getCollection(), new AfBulkImportListTransformer());
        $imports->setCollection(collect($fractal));

        return response()->json($imports, 200);
    }

    public function importModuleQuizzes(QuizImportRequest $request, int $courseId, int $levelId, int $courseModuleId)
    {
        $isBulkImportProcessing = $this->isBulkImportProcessing($courseModuleId, BulkImportEntityTypeData::MODULE);
        if ($isBulkImportProcessing)
            return response()->json(['errors' => $isBulkImportProcessing], 400);

        $userId = $request->user()->id;
        $bis = $this->afBulkImportQuizzesRepository->initImport($userId, $courseId, $courseModuleId, BulkImportEntityTypeData::MODULE);

        $tmpImportQuizzesDirPath = $this->afBulkImportQuizzesRepository->getTmpImportQuizzesPath($bis->id);
        $tmpImportQuizFileName = $this->afBulkImportQuizzesRepository->getTmpImportQuizFileName($courseModuleId, $request->file('file'));

        $this->afBulkImportQuizzesRepository->makeModuleImportIndexFile(
            $courseModuleId,
            $request->duration,
            $request->sample_size,
            $request->price,
            $tmpImportQuizzesDirPath,
            $tmpImportQuizFileName
        );

        $this->afBulkImportQuizzesRepository->makeImportReady($tmpImportQuizzesDirPath, $tmpImportQuizFileName, $request->file('file'));

        Storage::disk('s3')->putFileAs(AfBulkImportQuizzesRepository::getStoragePath($bis->id), storage_path('app/' . $tmpImportQuizzesDirPath . '.zip'), "$bis->id.zip");

        $this->afBulkImportQuizzesRepository->cleanup($tmpImportQuizzesDirPath);
        AfBulkImportQuizzesJob::dispatch($bis->id)->onQueue('bulk-import');

        return response()->json(['message' => 'Import is being processed'], 200);
    }

    public function getModuleBulkImports(int $courseId, int $levelId, int $courseModuleId)
    {
        $imports = $this->afBulkImportQuizzesRepository->getBulkImports($courseModuleId, BulkImportEntityTypeData::MODULE);

        $fractal = fractal($imports->getCollection(), new AfBulkImportListTransformer());
        $imports->setCollection(collect($fractal));

        return response()->json($imports, 200);
    }

    private function isBulkImportProcessing($entityId, $entityType)
    {
        $isBulkImportProcessing = null;
        $bis = $this->afBulkImportQuizzesRepository->getLatestBulkImport($entityId, $entityType);

        if ($bis && $bis->status == BulkImportStatusData::PENDING)
            $isBulkImportProcessing = 'Already initiated, pending processing';
        if ($bis && $bis->status == BulkImportStatusData::PROCESSING)
            $isBulkImportProcessing = 'Import is being processed';

        return $isBulkImportProcessing;
    }
}
