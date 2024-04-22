<?php

namespace App\Listeners;

use App\DataObject\PasswordHistoryData;
use App\Events\Users\IuUserCreated;
use App\Events\Users\UserPasswordUpdated;
use App\Mail\AgeVerificationEmail;
use App\Mail\VerificationEmail;
use App\Repositories\AuthenticationRepository;
use App\Repositories\IU\IuUserRepository;
use App\Repositories\PasswordHistoryRepository;
use App\Traits\UtilsTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class UserEventSubscriber implements ShouldQueue
{
    use UtilsTrait;

    private $iuUserRepository, $authRepository, $passwordHistoryRepository;
    /**
     * Create the event listener.
     *
     * @param IuUserRepository $iuUserRepository
     * @param AuthenticationRepository $authRepository
     * @param PasswordHistoryRepository $passwordHistoryRepository
     *
     * @return void
     */
    public function __construct(
        IuUserRepository $iuUserRepository,
        AuthenticationRepository $authRepository,
        PasswordHistoryRepository $passwordHistoryRepository
    ) {
        $this->iuUserRepository = $iuUserRepository;
        $this->authRepository = $authRepository;
        $this->passwordHistoryRepository = $passwordHistoryRepository;
    }

    /**
     * Handle the event IuUserCreated.
     *
     * @param  object  $event
     * @return void
     */
    public function handleIuAccountCreated($event)
    {
        $userProfile = $this->iuUserRepository->createUserProfile($event->user->id, $event->email, $event->dateOfBirth);
        $verifyUser = $this->authRepository->createVerifyUser($event->user);

        $this->passwordHistoryRepository->storeCurrentPassword($event->user->id, $event->password);

        Mail::to($event->email)->queue(new VerificationEmail($event->user, $verifyUser->token));

        if ($event->parentEmail) {
            $verifyUserAge = $this->authRepository->createVerifyUserAge($event->user->id);
            Mail::to($event->parentEmail)->queue(new AgeVerificationEmail($event->user, $userProfile, $verifyUserAge->token));
        }
    }

    /**
     * Handle the event UserPasswordUpdated.
     *
     * @param  object  $event
     * @return void
     */
    public function handleUserPasswordUpdated($event)
    {
        $this->passwordHistoryRepository->storeCurrentPassword($event->userId, $event->password);
        $this->passwordHistoryRepository->managePasswordHistoryDepth($event->userId, PasswordHistoryData::PASSWORD_HISTORY_DEPTH);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            IuUserCreated::class,
            [UserEventSubscriber::class, 'handleIuAccountCreated']
        );

        $events->listen(
            UserPasswordUpdated::class,
            [UserEventSubscriber::class, 'handleUserPasswordUpdated']
        );
    }
}
