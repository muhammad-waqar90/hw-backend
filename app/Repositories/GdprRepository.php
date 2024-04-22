<?php

namespace App\Repositories;

use App\DataObject\GDPRStatusData;
use App\Exports\Excel\GDPR\CertificatesExport;
use App\Exports\Excel\GDPR\CourseEnrollmentsExport;
use App\Exports\Excel\GDPR\ExamAccessesExport;
use App\Exports\Excel\GDPR\LectureNotesAccessesExport;
use App\Exports\Excel\GDPR\LessonNotesExport;
use App\Exports\Excel\GDPR\SupportTicketsExport;
use App\Exports\Excel\GDPR\UserAttemptedQuizzesExport;
use App\Exports\Excel\GDPR\UserNotificationsExport;
use App\Exports\Excel\GDPR\UserPlatformProgress;
use App\Exports\Excel\GDPR\CustomerInfoExport;
use App\Exports\Excel\GDPR\IdentityVerificationsExport;
use App\Exports\Excel\GDPR\RefundsExport;
use App\Exports\Excel\GDPR\UserInfoExport;
use App\Exports\Excel\GDPR\UserPurchasesExport;
use App\Models\UserGdprRequest;
use Illuminate\Support\Str;
use Storage;
use ZipArchive;

class GdprRepository
{

    private UserGdprRequest $userGdprRequest;

    public function __construct(UserGdprRequest $userGdprRequest)
    {
        $this->userGdprRequest = $userGdprRequest;
    }

    public function init($userId)
    {
        return $this->userGdprRequest->create(
            [
                'user_id'   => $userId,
                'uuid'      => Str::orderedUuid()->toString(),
                'status'    => GDPRStatusData::PROCESSING,
            ]
        );
    }

    public function getGdprRequest($userId, $uuid)
    {
        return $this->userGdprRequest->where('user_id', $userId)
            ->where('uuid', $uuid)
            ->first();
    }

    public function getAllUserGdprRequests($userId, $status = GDPRStatusData::READY)
    {
        return $this->userGdprRequest
            ->where('status', $status)
            ->where('user_id', $userId)
            ->get();
    }

    public function getGdprRequestByUuid(string $uuid)
    {
        return $this->userGdprRequest
            ->where('uuid', $uuid)
            ->first();
    }

    public function updateGdprRequestStatus($userId, $uuid, $status)
    {
        return $this->userGdprRequest
            ->where('user_id', $userId)
            ->where('uuid', $uuid)
            ->update([
                'status'    => $status
            ]);
    }

    public function markGdprRequestAsDownloaded($uuid)
    {
        return $this->userGdprRequest
            ->where('uuid', $uuid)
            ->update([
                'downloaded'    => 1
            ]);
    }

    public function generateCSVs($userId, $tmpExportGdprDirectory)
    {
        (new UserInfoExport($userId))->store($tmpExportGdprDirectory . '/user_info.csv');
        (new CustomerInfoExport($userId))->store($tmpExportGdprDirectory . '/customer_info.csv');
        (new UserPurchasesExport($userId))->store($tmpExportGdprDirectory . '/purchases.csv');
        (new RefundsExport($userId))->store($tmpExportGdprDirectory . '/refunds.csv');
        (new CertificatesExport($userId))->store($tmpExportGdprDirectory . '/certificates.csv');
        (new CourseEnrollmentsExport($userId))->store($tmpExportGdprDirectory . '/enrolled_courses.csv');
        (new LectureNotesAccessesExport($userId))->store($tmpExportGdprDirectory . '/lecture_notes_accesses.csv');
        (new LessonNotesExport($userId))->store($tmpExportGdprDirectory . '/lesson_notes.csv');
        (new UserNotificationsExport($userId))->store($tmpExportGdprDirectory . '/user_notifications.csv');
        (new SupportTicketsExport($userId, $tmpExportGdprDirectory))->store($tmpExportGdprDirectory . '/support_tickets.csv');
        (new UserAttemptedQuizzesExport($userId))->store($tmpExportGdprDirectory . '/user_quizzes.csv');
        (new ExamAccessesExport($userId))->store($tmpExportGdprDirectory . '/exam_accesses.csv');
        (new UserPlatformProgress($userId))->store($tmpExportGdprDirectory . '/user_platform_progress.csv');
        (new IdentityVerificationsExport($userId, $tmpExportGdprDirectory))->store($tmpExportGdprDirectory . '/identity_verifications.csv');
    }

    public function generateZipArchive($uuid, $tmpExportGdprDirectory)
    {
        $zip = new ZipArchive;
        if (true === ($zip->open(storage_path('app/'. $tmpExportGdprDirectory . '.zip'), ZipArchive::CREATE | ZipArchive::OVERWRITE))) {
            foreach (Storage::allFiles($tmpExportGdprDirectory) as $file) {
                $zip->addFile(storage_path('app/' . $file), $uuid . '/' . basename($file));
            }
            $zip->close();
        }
    }

    public function uploadZipToS3AndCleanLocalStorage($uuid, $tmpExportGdprDirectory)
    {
        Storage::disk(config('filesystems.cloud'))->putFileAs('GDPR/', storage_path('app/'. $tmpExportGdprDirectory . '.zip'), $uuid . '.zip');
        Storage::delete([$tmpExportGdprDirectory . '.zip']);
        Storage::deleteDirectory($tmpExportGdprDirectory);
    }

    public function removeExpiredGdprExports($expiredGdprRequests)
    {
        $expiredGdprZips = $this->getExpiredGdprZips($expiredGdprRequests->pluck('uuid')->toArray());
        Storage::disk('s3')->delete($expiredGdprZips);
        return $this->markGdprRequestsAsExpired($expiredGdprRequests->pluck('id'));
    }

    public function getExpiredGdprZips($uuids)
    {
        return array_filter(
            array_map(function ($uuid) {
                return 'GDPR/' . $uuid . '.zip';
            }, $uuids),
            function ($zip) {
                if (Storage::disk('s3')->exists($zip)) {
                    return $zip;
                }
            }
        );
    }

    public function markGdprRequestsAsExpired($ids)
    {
        return $this->userGdprRequest
            ->whereIn('id', $ids)->update([
                'status'    => GDPRStatusData::EXPIRED
            ]);
    }

    public function onExpiredRestoreUser($userId)
    {
        $gdprRequests = $this->getAllUserGdprRequests($userId);
        if ($gdprRequests->isEmpty()) return;

        return $this->removeExpiredGdprExports($gdprRequests);
    }
}
