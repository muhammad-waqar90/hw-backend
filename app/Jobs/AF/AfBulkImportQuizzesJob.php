<?php

namespace App\Jobs\AF;

use App\DataObject\AF\BulkImportStatusData;
use App\Exceptions\Quizzes\Imports\AbstractQuestionException;
use App\Imports\Excel\Quizzes\IndexImport;
use App\Models\BulkImportStatus;
use App\Repositories\AF\AfBulkImportQuizzesRepository;
use App\Traits\ZipTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AfBulkImportQuizzesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ZipTrait;

    private int $bisId;
    private BulkImportStatus $bis;
    private string $workingDirectory;

    /**
     * Create a new job instance.
     * @param $bisId
     */
    public function __construct($bisId)
    {
        $this->bisId = $bisId;

        $this->bis = AfBulkImportQuizzesRepository::getPendingBis($bisId);
        $this->workingDirectory = AfBulkImportQuizzesRepository::getStoragePath($this->bis->id);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $this->updateBisToProcessing($this->bis);

        $this->downloadZipFile();
        $this->unzipToCurrentFolder(AfBulkImportQuizzesRepository::getCourseQuizzesZipFullPath($this->bis->id));

        (new IndexImport($this->bis, $this->workingDirectory, 'index.xlsx'))->import($this->workingDirectory . '/index.xlsx');

        $this->cleanup($this->bis);
    }

    private function updateBisToProcessing($bis)
    {
        $bis->status = BulkImportStatusData::PROCESSING;
        $bis->save();

        return $bis;
    }

    private function downloadZipFile()
    {
        $file = Storage::disk('s3')->get(AfBulkImportQuizzesRepository::getStoragePath($this->bis->id) . "/$this->bisId.zip");
        Storage::disk('local')->put(AfBulkImportQuizzesRepository::getStoragePath($this->bis->id) . "/$this->bisId.zip", $file);
    }

    private function cleanup($bis, Throwable $exception = null)
    {
        if($exception)
            $this->handleException($bis, $exception);
        if(!$exception)
            $this->handleSuccess($bis);

        Storage::deleteDirectory(AfBulkImportQuizzesRepository::getStoragePath($this->bis->id));
        Storage::disk('s3')->deleteDirectory(AfBulkImportQuizzesRepository::getStoragePath($this->bis->id));
    }

    private function handleSuccess($bis)
    {
        $bis->status = BulkImportStatusData::COMPLETED;
        $bis->save();
    }

    private function handleException($bis, $exception)
    {
        $errors = null;
        if($exception instanceof AbstractQuestionException)
            $errors = $exception->formatError();
        else
            $errors = ['message' => Str::substr($exception->getMessage(), 0, 2000)];

        $bis->status = BulkImportStatusData::FAILED;
        $bis->errors = $errors;
        $bis->save();
    }

    public function failed(Throwable $exception = null)
    {
        $this->cleanup($this->bis, $exception);
    }
}
