<?php

namespace App\Repositories\AF;

use App\DataObject\AF\BulkImportStatusData;
use App\DataObject\AF\BulkImportTypeData;
use App\Imports\Excel\Quizzes\MakeIndexImportFile;
use App\Models\BulkImportStatus;
use App\Traits\ZipTrait;
use Illuminate\Support\Facades\Storage;

class AfBulkImportQuizzesRepository
{
    use ZipTrait;

    private BulkImportStatus $bulkImportStatus;

    public function __construct(BulkImportStatus $bulkImportStatus)
    {
        $this->bulkImportStatus = $bulkImportStatus;
    }

    public function initImport(int $userId, int $courseId, int $entityId, string $entityType, $type = BulkImportTypeData::QUIZ)
    {
        return $this->bulkImportStatus->create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'type' => $type,
            'status' => BulkImportStatusData::PENDING,
        ]);
    }

    public static function getLatestBulkImport($entityId, $entityType)
    {
        return BulkImportStatus::where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->where('type', BulkImportTypeData::QUIZ)
            ->latest()
            ->first();
    }

    public static function getPendingBis($id)
    {
        return BulkImportStatus::where('id', $id)
            ->where('type', BulkImportTypeData::QUIZ)
            ->where('status', BulkImportStatusData::PENDING)
            ->first();
    }

    public static function getStoragePath($bisId)
    {
        return "tmp/bulk/$bisId/quizzes";
    }

    public static function getCourseQuizzesZipFullPath($bisId)
    {
        return Storage::path(
            AfBulkImportQuizzesRepository::getStoragePath($bisId)
        )."/$bisId.zip";
    }

    public function getTmpImportQuizzesPath($bisId)
    {
        return "tmp/bulk/imports/$bisId";
    }

    public function getBulkImports($entityId, $entityType, $type = BulkImportTypeData::QUIZ)
    {
        return $this->bulkImportStatus
            ->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->where('type', $type)
            ->with('admin', function ($query) {
                $query->with('adminProfile');
            })
            ->latest('id')
            ->paginate(20);
    }

    public function cleanup($tmpExportDirectory)
    {
        Storage::deleteDirectory($tmpExportDirectory);
        Storage::delete($tmpExportDirectory.'.zip');
    }

    public function makeImportReady($tmpImportQuizzesDirPath, $tmpImportQuizFileName, $file)
    {
        Storage::disk('local')->putFileAs($tmpImportQuizzesDirPath, $file, $tmpImportQuizFileName);
        $this->zipToCurrentFolder($tmpImportQuizzesDirPath);
    }

    public function getTmpImportQuizFileName($entityId, $file)
    {
        return $entityId.'_'.$file->getClientOriginalName();
    }

    public function makeLessonImportIndexFile($lessonId, $duration, $sampleSize, $tmpImportQuizzesDirPath, $tmpImportQuizFileName)
    {
        (new makeIndexImportFile($lessonId, '', '', $tmpImportQuizFileName, $sampleSize, $duration, ''))->store($tmpImportQuizzesDirPath.'/index.xlsx');
    }

    public function makeModuleImportIndexFile($moduleId, $duration, $sampleSize, $price, $tmpImportQuizzesDirPath, $tmpImportQuizFileName)
    {
        (new makeIndexImportFile('', $moduleId, '', $tmpImportQuizFileName, $sampleSize, $duration, $price))->store($tmpImportQuizzesDirPath.'/index.xlsx');
    }
}
