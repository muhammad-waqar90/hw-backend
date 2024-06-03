<?php

namespace App\Listeners;

use App\DataObject\CertificateEntityData;
use App\DataObject\Notifications\NotificationTypeData;
use App\Events\Certificates\CourseCompleted;
use App\Events\Certificates\CourseLevelCompleted;
use App\Events\Certificates\CourseModuleCompleted;
use App\Mail\IU\Certificate\IuCertificateEmail;
use App\Repositories\IU\IuCertificateRepository;
use App\Repositories\IU\IuUserRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CertificateEventSubscriber
{
    /**
     * @var IuCertificateRepository
     */
    private $iuCertificateRepository;

    /**
     * @var NotificationRepository
     */
    private $notificationRepository;

    /**
     * @var IuUserRepository
     */
    private $iuUserRepository;

    /**
     * CertificateEventSubscriber constructor.
     */
    public function __construct(IuCertificateRepository $iuCertificateRepository, NotificationRepository $notificationRepository, IuUserRepository $iuUserRepository)
    {
        $this->iuCertificateRepository = $iuCertificateRepository;
        $this->notificationRepository = $notificationRepository;
        $this->iuUserRepository = $iuUserRepository;
    }

    /**
     * Handle Course Module Completed
     */
    public function handleCourseModuleCompleted($event)
    {
        DB::beginTransaction();
        try {
            // create certificate
            $certificate = $this->iuCertificateRepository
                ->createCertificate(
                    $event->userId,
                    $event->entityId,
                    CertificateEntityData::ENTITY_COURSE_MODULE
                );

            // create notification
            $this->notificationRepository->createNotification(
                $event->userId,
                Lang::get('notifications.certificates.title'),
                Lang::get('notifications.certificates.certificate', ['entity_type' => 'course module']),
                NotificationTypeData::CERTIFICATE,
                $this->generateAction($certificate->id)
            );

            // send email
            $iuUser = $this->iuUserRepository->getUser($event->userId, true);
            Mail::to($iuUser->userProfile->email)->queue(new IuCertificateEmail($iuUser, $certificate->id, 'course module'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: CertificateEventSubscriber@handleCourseModuleCompleted', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    /**
     * Handle Course Level Completed
     */
    public function handleCourseLevelCompleted($event)
    {
        DB::beginTransaction();
        try {
            // create certificate
            $certificate = $this->iuCertificateRepository
                ->createCertificate(
                    $event->userId,
                    $event->entityId,
                    CertificateEntityData::ENTITY_COURSE_LEVEL
                );

            // create notification
            $this->notificationRepository->createNotification(
                $event->userId,
                Lang::get('notifications.certificates.title'),
                Lang::get('notifications.certificates.certificate', ['entity_type' => 'course level']),
                NotificationTypeData::CERTIFICATE,
                $this->generateAction($certificate->id)
            );

            // send email
            $iuUser = $this->iuUserRepository->getUser($event->userId, true);
            Mail::to($iuUser->userProfile->email)->queue(new IuCertificateEmail($iuUser, $certificate->id, 'course level'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: CertificateEventSubscriber@handleCourseLevelCompleted', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    /**
     * Handle Course Completed
     */
    public function handleCourseCompleted($event)
    {
        DB::beginTransaction();
        try {
            // create certificate
            $certificate = $this->iuCertificateRepository
                ->createCertificate(
                    $event->userId,
                    $event->entityId,
                    CertificateEntityData::ENTITY_COURSE
                );

            // create notification
            $this->notificationRepository->createNotification(
                $event->userId,
                Lang::get('notifications.certificates.title'),
                Lang::get('notifications.certificates.certificate', ['entity_type' => 'course']),
                NotificationTypeData::CERTIFICATE,
                $this->generateAction($certificate->id)
            );

            // send email
            $iuUser = $this->iuUserRepository->getUser($event->userId, true);
            Mail::to($iuUser->userProfile->email)->queue(new IuCertificateEmail($iuUser, $certificate->id, 'course'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: CertificateEventSubscriber@handleCourseCompleted', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    /**
     * generate action for certificate notifications
     */
    public function generateAction($id)
    {
        return [
            'redirect' => [
                'id' => $id,
            ],
        ];
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events)
    {
        $events->listen(
            CourseModuleCompleted::class,
            [CertificateEventSubscriber::class, 'handleCourseModuleCompleted']
        );

        $events->listen(
            CourseLevelCompleted::class,
            [CertificateEventSubscriber::class, 'handleCourseLevelCompleted']
        );

        $events->listen(
            CourseCompleted::class,
            [CertificateEventSubscriber::class, 'handleCourseCompleted']
        );
    }
}
