<?php

namespace App\Http;

use App\Http\Middleware\AF\AfCanReplyToTicket;
use App\Http\Middleware\AF\AfCanUpdateModuleHasExam;
use App\Http\Middleware\AF\AfCanUploadLessonQuiz;
use App\Http\Middleware\AF\AfCanUploadModuleQuiz;
use App\Http\Middleware\CheckActive;
use App\Http\Middleware\CheckOrPermission;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\isCourseStatus;
use App\Http\Middleware\IsUserDeleted;
use App\Http\Middleware\IU\CheckIfGdprDataIsProcessing;
use App\Http\Middleware\IU\IuCanAccessEbook;
use App\Http\Middleware\IU\IuCanAccessLevelEbookList;
use App\Http\Middleware\IU\IuCanAccessModule;
use App\Http\Middleware\IU\IuCanAccessTicket;
use App\Http\Middleware\IU\IuCanReplyToTicket;
use App\Http\Middleware\IU\IuHasCompletedProfile;
use App\Http\Middleware\IU\IuHasEntityExamAccess;
use App\Http\Middleware\IU\IuIdentityVerified;
use App\Http\Middleware\IU\IuUserCanAccessEntityQuiz;
use App\Http\Middleware\IU\IuUserCanAccessLesson;
use App\Http\Middleware\IU\IuUserCanUpdatePaymentMethod;
use App\Http\Middleware\IU\IuUserOwnsCertificate;
use App\Http\Middleware\IU\IuUserOwnsCourse;
use App\Http\Middleware\PasswordProtected;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\EncryptCookies::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \App\Http\Middleware\SanitizeRequest::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\ResponseWithHeaders::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        //Custom middlewares
        'role' => CheckRole::class,
        'permission' => CheckPermission::class,
        'orPermission' => CheckOrPermission::class,
        'IuOwnsCourse' => IuUserOwnsCourse::class,
        'IuCanAccessLesson' => IuUserCanAccessLesson::class,
        'IuCanAccessEntityQuiz' => IuUserCanAccessEntityQuiz::class,
        'IuHasEntityExamAccess' => IuHasEntityExamAccess::class,
        'AfCanReplyToTicket' => AfCanReplyToTicket::class,
        'IuCanAccessTicket' => IuCanAccessTicket::class,
        'IuCanReplyToTicket' => IuCanReplyToTicket::class,
        'passwordProtected' => PasswordProtected::class,
        'checkActive' => CheckActive::class,
        'IuCanAccessEbook' => IuCanAccessEbook::class,
        'IuCanAccessModule' => IuCanAccessModule::class,
        'IuCanAccessLevelEbookList' => IuCanAccessLevelEbookList::class,
        'IuUserOwnsCertificate' => IuUserOwnsCertificate::class,
        'IuUserCanUpdatePaymentMethod' => IuUserCanUpdatePaymentMethod::class,
        'IuIdentityVerified' => IuIdentityVerified::class,
        'IuHasCompletedProfile' => IuHasCompletedProfile::class,
        'CheckIfGdprDataIsProcessing' => CheckIfGdprDataIsProcessing::class,
        'isCourseStatus' => isCourseStatus::class,
        'IsUserDeleted' => IsUserDeleted::class,
        'AfCanUploadLessonQuiz' => AfCanUploadLessonQuiz::class,
        'afCanUpdateModuleHasExam' => AfCanUpdateModuleHasExam::class,
        'afCanUploadModuleQuiz' => AfCanUploadModuleQuiz::class,
    ];
}
