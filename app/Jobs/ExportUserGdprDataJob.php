<?php

namespace App\Jobs;

use App\DataObject\GDPRStatusData;
use App\Mail\IU\GDPR\IuGdprExportDataEmail;
use App\Repositories\GdprRepository;
use App\Repositories\IU\IuUserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ExportUserGdprDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;

    private $uuid;

    private $tmpExportGdprDirectory;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $uuid)
    {
        $this->userId = $userId;
        $this->uuid = $uuid;
        $this->tmpExportGdprDirectory = 'tmp/exports/gdpr/'.$uuid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(GdprRepository $gdprRepository, IuUserRepository $iuUserRepository)
    {
        $user = $iuUserRepository->getUser($this->userId, true);
        if (! $user) {
            return false;
        }

        $this->gdprRequest = $gdprRepository->getGdprRequest($user->id, $this->uuid);
        if (! $this->gdprRequest && $this->gdprRequest->status !== GDPRStatusData::PROCESSING) {
            return false;
        }

        $gdprRepository->generateCSVs($user->id, $this->tmpExportGdprDirectory);
        $gdprRepository->generateZipArchive($this->uuid, $this->tmpExportGdprDirectory);
        $gdprRepository->uploadZipToS3AndCleanLocalStorage($this->uuid, $this->tmpExportGdprDirectory);
        $gdprRepository->updateGdprRequestStatus($user->id, $this->uuid, GDPRStatusData::READY);

        Mail::to($user->userProfile->email)->queue(new IuGdprExportDataEmail($user, $this->uuid));
    }
}
