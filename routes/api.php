<?php

use App\DataObject\QuizData;
use App\DataObject\RoleData;
use App\DataObject\PermissionData;
use Illuminate\Support\Facades\Route;
use App\DataObject\AF\CourseStatusData;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AF\AfFaqController;
use App\Http\Controllers\GU\GuFaqController;
use App\Http\Controllers\IU\IuFaqController;
use App\Http\Controllers\AF\AfUserController;
use App\Http\Controllers\GU\GuCartController;
use App\Http\Controllers\IU\IuGdprController;
use App\Http\Controllers\IU\IuQuizController;
use App\Http\Controllers\AF\AfEventController;
use App\Http\Controllers\GU\GuEbookController;
use App\Http\Controllers\IU\IuEbookController;
use App\Http\Controllers\AF\AfAdvertController;
use App\Http\Controllers\AF\AfCouponController;
use App\Http\Controllers\AF\AfCourseController;
use App\Http\Controllers\AF\AfLessonController;
use App\Http\Controllers\AF\AfTicketController;
use App\Http\Controllers\GU\GuCourseController;
use App\Http\Controllers\GU\GuTicketController;
use App\Http\Controllers\IU\IuAdvertController;
use App\Http\Controllers\IU\IuCouponController;
use App\Http\Controllers\IU\IuCourseController;
use App\Http\Controllers\IU\IuEventsController;
use App\Http\Controllers\IU\IuLessonController;
use App\Http\Controllers\IU\IuTicketController;
use App\Http\Controllers\AF\AfProductController;
use App\Http\Controllers\GU\GuProductController;
use App\Http\Controllers\IU\IuPaymentController;
use App\Http\Controllers\IU\IuProductController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AF\AfPurchaseController;
use App\Http\Controllers\HA\PermissionController;
use App\Http\Controllers\IU\IuLessonQaController;
use App\Http\Controllers\IU\IuPurchaseController;
use App\Http\Controllers\AF\AfLessonFaqController;
use App\Http\Controllers\AF\AfBulkImportController;
use App\Http\Controllers\AF\AfInAppTiersController;
use App\Http\Controllers\AF\AfCourseLevelController;
use App\Http\Controllers\AF\AfLessonEbookController;
use App\Http\Controllers\IU\IuCertificateController;
use App\Http\Controllers\IU\IuSalaryScaleController;
use App\Http\Controllers\IU\IuUserProfileController;
use App\Http\Controllers\AF\AfCourseModuleController;
use App\Http\Controllers\AF\AfNotificationController;
use App\Http\Controllers\IU\IuNotificationController;
use App\Http\Controllers\AF\AfCategoryController;
use App\Http\Controllers\HA\HaAdminManipulationController;
use App\Http\Controllers\MA\MaAdminManipulationController;
use App\Http\Controllers\AF\AfGlobalNotificationsController;
use App\Http\Controllers\AF\AfQuizController;
use App\Http\Controllers\IU\IuGlobalNotificationsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

/*
|--------------------------------------------------------------------------
| Notes
|--------------------------------------------------------------------------
|
| Wildcard routes should have their patterns defined in RouteServiceProvider->configurePatterns()
| Example per route config ->where(['courseId' => '[0-9]+', 'lessonId' => '[0-9]+'])
| Example per group config 'where' => ['courseId' => '[0-9]+', 'lessonId' => '[0-9]+']
 */

Route::group([
    'prefix' => 'auth',
    'middleware' => 'throttle:7,1',
], function () {
    Route::post('login', [AuthController::class, 'webLogin']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify', [AuthController::class, 'verify']);
    Route::post('verify-age', [AuthController::class, 'verifyAge']);
    Route::post('verify/resend', [AuthController::class, 'resendVerificationCode']);
    Route::post('verify/parent/resend', [AuthController::class, 'resendParentVerificationCode']);
    Route::post('username/forgot/request', [AuthController::class, 'requestForgotUsername']);
    Route::post('restore', [AuthController::class, 'restore']);

    Route::group([
        'prefix' => 'password-reset',
    ], function () {
        Route::post('request', [AuthController::class, 'requestPasswordReset']);
        Route::post('check', [AuthController::class, 'checkPasswordReset']);
        Route::put('', [AuthController::class, 'updatePassword']);
    });

    Route::group([
        'prefix' => 'mobile',
    ], function () {
        Route::post('login', [AuthController::class, 'mobileLogin']);
    });
});
Route::post('auth/refresh', [AuthController::class, 'refresh']);

Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'auth',
], function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'notifications',
], function () {
    Route::put('{id}/read', [NotificationController::class, 'markNotificationRead']);
});

Route::group([
    'middleware' => ['auth:api', 'checkActive', 'role:' . RoleData::INDEPENDENT_USER],
    'prefix' => 'iu',
], function () {
    Route::delete('me', [AuthController::class, 'delete']);
    Route::put('change-password', [AuthController::class, 'changeIuPassword']);

    Route::group([
        'prefix' => 'courses',
    ], function () {
        Route::get('dashboard', [IuCourseController::class, 'getDashboard']);
        Route::get('available', [IuCourseController::class, 'getIuCourseAvailableList']);
        Route::get('owned', [IuCourseController::class, 'getIuCourseOwnedList']);
        Route::get('coming-soon', [IuCourseController::class, 'getIuCourseComingSoonList']);
        //Get list of course_modules that contain ebooks
        Route::get('{courseId}/level/{level}/ebooks', [IuEbookController::class, 'getEbookListPerLevel'])
            ->middleware('IuCanAccessLevelEbookList');

        Route::get('{id}', [IuCourseController::class, 'getIuCourse']);
        Route::get('{courseId}/level/{value}', [IuCourseController::class, 'getIuCourseLevel']);
        Route::group([
            'middleware' => 'IuOwnsCourse',
            'prefix' => '{courseId}',
        ], function () {
            Route::group([
                'prefix' => 'lessons',
            ], function () {
                Route::get('ongoing', [IuLessonController::class, 'getOngoingLessons']);
                Route::group([
                    'middleware' => 'IuCanAccessLesson',
                    'prefix' => '{lessonId}',
                ], function () {
                    Route::get('', [IuLessonController::class, 'get']);
                    Route::get('note', [IuLessonController::class, 'getLessonNote']);
                    Route::post('note', [IuLessonController::class, 'updateLessonNote']);
                    Route::post('video', [IuLessonController::class, 'updateVideoProgress']);

                    //Ebooks
                    Route::group([
                        'prefix' => 'ebooks',
                    ], function () {
                        Route::get('', [IuEbookController::class, 'get'])->middleware('IuCanAccessEbook');
                        Route::post('dismiss', [IuEbookController::class, 'dismiss']);
                    });

                    //Lesson Q&A
                    Route::group([
                        'prefix' => 'qas',
                    ], function () {
                        Route::post('', [IuLessonQaController::class, 'createLessonQaTicket']);
                        Route::group([
                            'prefix' => 'me',
                        ], function () {
                            Route::get('', [IuLessonQaController::class, 'getMyLessonQaTicketList']);
                            Route::get('latest', [IuLessonQaController::class, 'getMyLatestLessonQaTicket']);
                        });
                    });

                    //Quizzes for lessons
                    Route::group([
                        'middleware' => 'IuCanAccessEntityQuiz:' . QuizData::ENTITY_LESSON,
                        'prefix' => 'quiz',
                    ], function () {
                        Route::get('', [IuQuizController::class, 'getLessonQuiz']);
                        Route::post('', [IuQuizController::class, 'submitLessonQuiz']);
                        Route::get('attempt', [IuQuizController::class, 'getLessonQuizAttempt']);
                    });
                });
            });

            Route::group([
                'prefix' => 'course-modules/{courseModuleId}',
            ], function () {
                Route::get('lessons', [IuLessonController::class, 'getAllLessonsOfModule']);

                //Quizzes for course modules
                Route::group([
                    'middleware' => 'IuCanAccessEntityQuiz:' . QuizData::ENTITY_COURSE_MODULE,
                    'prefix' => 'quiz',
                ], function () {
                    Route::group([
                        'middleware' => 'IuHasEntityExamAccess:' . QuizData::ENTITY_COURSE_MODULE,
                    ], function () {
                        Route::post('', [IuQuizController::class, 'submitCourseModuleQuiz']);
                        Route::get('', [IuQuizController::class, 'getCourseModuleQuiz']);
                    });
                    Route::get('attempt', [IuQuizController::class, 'getCourseModuleQuizAttempt']);
                    Route::get('access', [IuQuizController::class, 'getCourseModuleQuizAccess']);
                });
            });

            //Quizzes for course levels
            Route::group([
                'middleware' => 'IuCanAccessEntityQuiz:' . QuizData::ENTITY_COURSE_LEVEL,
                'prefix' => 'course-levels/{courseLevelId}/quiz',
            ], function () {
                Route::group([
                    'middleware' => 'IuHasEntityExamAccess:' . QuizData::ENTITY_COURSE_LEVEL,
                ], function () {
                    Route::get('', [IuQuizController::class, 'getCourseLevelQuiz']);
                    Route::post('', [IuQuizController::class, 'submitCourseLevelQuiz']);
                });
                Route::get('attempt', [IuQuizController::class, 'getCourseLevelQuizAttempt']);
            });
        });
    });
    Route::group([
        'prefix' => 'tickets',
    ], function () {
        Route::post('', [IuTicketController::class, 'submitTicket']);
        Route::get('subjects', [IuTicketController::class, 'getTicketSubjectList']);
        Route::get('subjects/{id}', [IuTicketController::class, 'getTicketSubject']);
        Route::get('me', [IuTicketController::class, 'getMyTicketList']);
        Route::get('{id}', [IuTicketController::class, 'getTicket'])->middleware('IuCanAccessTicket');
        Route::post('{id}/messages', [IuTicketController::class, 'replyToTicket'])->middleware('IuCanReplyToTicket');
        Route::put('{id}/resolve', [IuTicketController::class, 'resolveTicket'])->middleware('IuCanReplyToTicket');
        Route::put('{id}/reopen', [IuTicketController::class, 'reopenTicket'])->middleware('IuCanAccessTicket');
    });
    Route::group([
        'prefix' => 'profile',
    ], function () {
        Route::get('me', [IuUserProfileController::class, 'get']);
        Route::post('me', [IuUserProfileController::class, 'update']);
        Route::put('update-address', [IuUserProfileController::class, 'updateUserAddress']);
        Route::group([
            'prefix' => 'identity',
        ], function () {
            Route::get('', [IuUserProfileController::class, 'getIdentity']);
            Route::post('', [IuUserProfileController::class, 'updateOrCreateIdentity']);
        });
        Route::group(['prefix' => 'salary-scale'], function () {
            Route::put('update-flag', [IuUserProfileController::class, 'updateEnableSalaryScaleFlag']);
        });
    });
    Route::group([
        'prefix' => 'faqs',
    ], function () {
        Route::group([
            'prefix' => 'categories',
        ], function () {
            Route::get('', [IuFaqController::class, 'getRootFaqCategoryList']);
            Route::get('{id}', [IuFaqController::class, 'getSubFaqCategoryList']);
            Route::get('{id}/items', [IuFaqController::class, 'getFaqForCategory']);
        });
        Route::get('', [IuFaqController::class, 'searchFaq']);
        Route::get('{id}', [IuFaqController::class, 'getFaq']);
    });
    Route::group([
        'prefix' => 'payments',
    ], function () {
        Route::get('setup', [IuPaymentController::class, 'getSetupIntent']);
        Route::get('', [IuPaymentController::class, 'getPaymentMethod']);
        Route::post('', [IuPaymentController::class, 'updatePaymentMethod']);
        Route::delete('', [IuPaymentController::class, 'deletePaymentMethod']);
    });
    Route::group([
        'prefix' => 'purchases',
    ], function () {
        Route::get('history', [IuPurchaseController::class, 'getPurchaseHistory']);
        Route::group([
            'prefix' => 'cart',
        ], function () {
            Route::post('checkout', [IuPurchaseController::class, 'cartCheckout']);
        });
    });
    Route::group([
        'prefix' => 'certificates',
        'middleware' => ['IuIdentityVerified', 'IuHasCompletedProfile'],
    ], function () {
        Route::get('', [IuCertificateController::class, 'getMyCertificatesList']);
        Route::group([
            'middleware' => 'IuUserOwnsCertificate',
            'prefix' => '{id}',
        ], function () {
            Route::get('', [IuCertificateController::class, 'getCertificate']);
            Route::get('download', [IuCertificateController::class, 'downloadCertificate']);
        });
    });
    Route::group([
        'prefix' => 'global-notifications',
    ], function () {
        Route::put('modal/read', [IuGlobalNotificationsController::class, 'markGlobalNotificationsModalRead']);
        Route::get('{id}', [IuGlobalNotificationsController::class, 'getGlobalNotification']);
        Route::put('{id}/read', [IuGlobalNotificationsController::class, 'markGlobalNotificationRead']);
    });
    Route::group([
        'prefix' => 'notifications',
    ], function () {
        Route::get('me', [IuNotificationController::class, 'getMyNotificationList']);
        Route::put('all/read', [IuNotificationController::class, 'markAllNotificationsRead']);
    });
    Route::group([
        'prefix' => 'adverts',
    ], function () {
        Route::get('', [IuAdvertController::class, 'getAdvertList']);
    });
    Route::group([
        'prefix' => 'events',
    ], function () {
        Route::get('', [IuEventsController::class, 'getEventList']);
        Route::get('{id}', [IuEventsController::class, 'getEvent']);
    });

    // Routes for Products
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [IuProductController::class, 'fetch']);
        Route::get('available-books', [IuProductController::class, 'availableBooks']);
        Route::get('single-book', [IuProductController::class, 'singleBook']);
        Route::get('single-product', [IuProductController::class, 'singleProduct']);
        Route::get('top-books', [IuProductController::class, 'topBooks']);
    });

    Route::group([
        'prefix' => 'coupons/redeem'
    ], function () {
        Route::post('can', [IuCouponController::class, 'canRedeem']);
    });

    // Routes for IU Salary Scale
    Route::group(['prefix' => 'salary-scales'], function () {
        Route::get('discounted-countries', [IuSalaryScaleController::class, 'getDiscountedCountryList']);
        Route::post('', [IuSalaryScaleController::class, 'createSalaryScale']);
        Route::put('', [IuSalaryScaleController::class, 'updateSalaryScale']);
    });
});

Route::group([
    'middleware' => ['auth:api', 'checkActive', 'role:' . RoleData::HEAD_ADMIN],
    'prefix' => 'ha',
], function () {
    Route::group([
        'prefix' => 'admins',
    ], function () {
        Route::post('', [HaAdminManipulationController::class, 'createAdmin']);
        Route::get('', [HaAdminManipulationController::class, 'getAdminList']);
        Route::get('all', [HaAdminManipulationController::class, 'getAllAdmins']);
        Route::put('{id}/activate', [HaAdminManipulationController::class, 'activateAdmin']);
        Route::put('{id}/deactivate', [HaAdminManipulationController::class, 'deactivateAdmin']);
        Route::get('{id}', [HaAdminManipulationController::class, 'getAdmin']);
        Route::put('{id}', [HaAdminManipulationController::class, 'updateAdmin']);
        Route::delete('{id}', [HaAdminManipulationController::class, 'deleteAdmin']);
    });
    Route::group([
        'prefix' => 'permissions',
    ], function () {
        Route::get('', [PermissionController::class, 'getPermissionList']);
        Route::group([
            'prefix' => 'groups',
        ], function () {
            Route::post('', [PermissionController::class, 'createPermGroup']);
            Route::get('', [PermissionController::class, 'getPermGroupList']);
            Route::get('{id}', [PermissionController::class, 'getPermGroup']);
            Route::put('{id}', [PermissionController::class, 'updatePermGroup']);
            Route::delete('{id}', [PermissionController::class, 'deletePermGroup']);
        });
    });
});

Route::group([
    'middleware' => ['auth:api', 'checkActive', 'role:' . RoleData::ADMIN],
    'prefix' => 'af',
], function () {
    Route::group([
        'prefix' => 'tickets',
    ], function () {
        Route::get('categories', [AfTicketController::class, 'getTicketCategories']);
        Route::group([
            'middleware' => ['permission:' . PermissionData::TICKET_SUBJECT_MANAGEMENT],
            'prefix' => 'subjects',
        ], function () {
            Route::post('', [AfTicketController::class, 'createTicketSubject']);
            Route::get('', [AfTicketController::class, 'getTicketSubjectList']);
            Route::get('{id}', [AfTicketController::class, 'getTicketSubject']);
            Route::put('{id}', [AfTicketController::class, 'updateTicketSubject']);
            Route::delete('{id}', [AfTicketController::class, 'deleteTicketSubject']);
        });
        Route::group([
            'middleware' => ['orPermission:' . PermissionData::TICKET_SYSTEM_MANAGEMENT . ',' . PermissionData::TICKET_CONTENT_MANAGEMENT . ',' . PermissionData::TICKET_REFUND_MANAGEMENT . ',' . PermissionData::TICKET_LESSON_QA_MANAGEMENT],
        ], function () {
            Route::get('', [AfTicketController::class, 'getTicketList']);
            Route::get('me', [AfTicketController::class, 'getMyTicketList']);
            Route::put('{id}/categories', [AfTicketController::class, 'updateTicketCategory']);
            Route::put('{id}/claim', [AfTicketController::class, 'claimTicket']);
            Route::put('{id}/on-hold', [AfTicketController::class, 'onHoldTicket'])->middleware('AfCanReplyToTicket');
            Route::put('{id}/unclaim', [AfTicketController::class, 'unclaimTicket'])->middleware('AfCanReplyToTicket');
            Route::post('{id}/messages', [AfTicketController::class, 'replyToTicket'])->middleware('AfCanReplyToTicket');
            Route::put('{id}/resolve', [AfTicketController::class, 'resolveTicket'])->middleware('AfCanReplyToTicket');
            Route::get('{id}', [AfTicketController::class, 'getTicket']);
            Route::post('{id}/qa', [AfTicketController::class, 'saveTicketAsLessonFaq']);
        });
    });
    Route::group([
        'prefix' => 'faqs',
    ], function () {
        Route::group([
            'prefix' => 'categories',
            'middleware' => ['permission:' . PermissionData::FAQ_CATEGORY_MANAGEMENT],
        ], function () {
            Route::get('', [AfFaqController::class, 'getFaqCategoryList']);
            Route::post('', [AfFaqController::class, 'createFaqCategory']);
            Route::get('root', [AfFaqController::class, 'getRootFaqCategoryList']);
            Route::put('{id}', [AfFaqController::class, 'updateFaqCategory']);
            Route::get('{id}', [AfFaqController::class, 'getFaqCategory']);
            Route::delete('{id}', [AfFaqController::class, 'deleteFaqCategory']);
            Route::put('{id}/publish', [AfFaqController::class, 'publishFaqCategory']);
            Route::put('{id}/unpublish', [AfFaqController::class, 'unpublishFaqCategory']);
        });
        Route::group([
            'middleware' => ['permission:' . PermissionData::FAQ_MANAGEMENT],
        ], function () {
            Route::get('', [AfFaqController::class, 'getFaqList']);
            Route::post('', [AfFaqController::class, 'createFaq']);
            Route::get('categories/sub', [AfFaqController::class, 'getFaqSubCategoryList']);
            Route::get('{id}', [AfFaqController::class, 'getFaq']);
            Route::put('{id}', [AfFaqController::class, 'updateFaq']);
            Route::delete('{id}', [AfFaqController::class, 'deleteFaq']);
            Route::put('{id}/publish', [AfFaqController::class, 'publishFaq']);
            Route::put('{id}/unpublish', [AfFaqController::class, 'unpublishFaq']);
        });
    });
    Route::group([
        'prefix' => 'users',
        'middleware' => ['permission:' . PermissionData::USER_MANAGEMENT],
    ], function () {
        Route::get('', [AfUserController::class, 'getUsersList']);
        Route::get('{id}', [AfUserController::class, 'getUser']);
        Route::get('{id}/courses', [AfUserController::class, 'getUserCourses']);
        Route::put('{id}/enable', [AfUserController::class, 'enableUser'])->middleware('IsUserDeleted');
        Route::put('{id}/disable', [AfUserController::class, 'disableUser'])->middleware('IsUserDeleted');
        Route::group([
            'prefix' => '{id}/purchases',
            'middleware' => ['permission:' . PermissionData::VIEW_USERS_PURCHASE_HISTORY],
        ], function () {
            Route::get('', [AfUserController::class, 'getUserPurchases']);
            Route::get('unselectedEbooks/{items}', [AfUserController::class, 'getUnselectedEbooks']);
        });
        Route::delete('{id}', [AfUserController::class, 'deleteUser'])->middleware(['permission:' . PermissionData::DELETE_USERS]);
        Route::group([
            'prefix' => '{id}/gdpr',
            'middleware' => ['permission:' . PermissionData::GDPR_MANAGEMENT],
        ], function () {
            Route::post('export', [AfUserController::class, 'exportUserGDPRData'])->middleware(['CheckIfGdprDataIsProcessing', 'IsUserDeleted']);
        });
    });
    Route::group([
        'prefix' => 'categories',
        'middleware' => ['permission:' . PermissionData::CATEGORY_MANAGEMENT],
    ], function () {
        Route::get('', [AfCategoryController::class, 'getCategoryListDetailed']);
        Route::get('root', [AfCategoryController::class, 'getRootCategoryList']);
        Route::get('root/{id}/children', [AfCategoryController::class, 'getChildCategoriesForRootCategory']);
        Route::post('', [AfCategoryController::class, 'createCategory']);
        Route::get('{id}', [AfCategoryController::class, 'getCategory']);
        Route::put('{id}', [AfCategoryController::class, 'updateCategory']);
        Route::delete('{id}', [AfCategoryController::class, 'deleteCategory']);
    });
    Route::get('categories/filter', [AfCategoryController::class, 'getCategoryList'])->middleware(['permission:' . PermissionData::COURSE_MANAGEMENT . '|' . PermissionData::PHYSICAL_PRODUCT_MANAGEMENT]);
    Route::group([
        'prefix' => 'courses',
    ], function () {
        Route::group([
            'prefix' => 'filter',
            'middleware' => ['permission:' . PermissionData::USER_MANAGEMENT],
        ], function () {
            Route::get('', [AfCourseController::class, 'getCoursesList']);
            Route::get('{id}', [AfCourseController::class, 'getCourse']);
        });
        Route::group([
            'middleware' => ['permission:' . PermissionData::COURSE_MANAGEMENT],
        ], function () {
            Route::get('unboundedBooks/filter', [AfProductController::class, 'getAllUnboundedBooks']);
            Route::get('', [AfCourseController::class, 'getCoursesListDetailed']);
            Route::post('', [AfCourseController::class, 'createCourse']);
            Route::group([
                'prefix' => '{id}',
            ], function () {
                Route::get('validate', [AfCourseController::class, 'validateCourse']);
                Route::get('', [AfCourseController::class, 'getCourseDetailed']);
                Route::post('', [AfCourseController::class, 'updateCourse']);
                Route::delete('', [AfCourseController::class, 'deleteCourse'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);

                Route::group([
                    'prefix' => 'bulk',
                ], function () {
                    Route::get('quizzes', [AfBulkImportController::class, 'getCourseBulkImports']);
                    Route::group([
                        'middleware' => ['permission:' . PermissionData::BULK_UPLOAD_QUIZZES],
                    ], function () {
                        Route::post('quizzes', [AfBulkImportController::class, 'importCourseQuizzes'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);
                    });
                });

                Route::group([
                    'prefix' => 'status',
                    'middleware' => ['permission:' . PermissionData::UPDATE_COURSE_STATUS],
                ], function () {
                    Route::put('publish', [AfCourseController::class, 'publishCourse']);
                    Route::put('unpublish', [AfCourseController::class, 'unpublishCourse']);
                    Route::put('draft', [AfCourseController::class, 'draftCourse']);
                    Route::put('coming-soon', [AfCourseController::class, 'markCourseAsComingSoon']);
                });

                Route::group([
                    'prefix' => 'levels',
                ], function () {
                    Route::put('{levelId}', [AfCourseLevelController::class, 'updateLevel']);
                    Route::group([
                        'middleware' => ['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON],
                    ], function () {
                        Route::post('', [AfCourseLevelController::class, 'createLevel']);
                        Route::delete('{levelId}', [AfCourseLevelController::class, 'deleteLevel']);
                    });

                    Route::group([
                        'prefix' => '{levelId}/modules',
                    ], function () {
                        Route::post('', [AfCourseModuleController::class, 'createModule'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);
                        Route::put('sort', [AfCourseModuleController::class, 'sortModule'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);
                        Route::post('{courseModuleId}', [AfCourseModuleController::class, 'updateModule'])->middleware('afCanUpdateModuleHasExam');
                        Route::delete('{moduleIds}', [AfCourseModuleController::class, 'deleteModule'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);
                        Route::group([
                            'prefix' => '{courseModuleId}/quizzes',
                        ], function () {
                            Route::get('', [AfQuizController::class, 'getModuleQuiz']);
                            Route::group([
                                'prefix' => 'bulk',
                            ], function () {
                                Route::get('', [AfBulkImportController::class, 'getModuleBulkImports']);
                                Route::group([
                                    'middleware' => ['permission:' . PermissionData::BULK_UPLOAD_QUIZZES, 'afCanUploadModuleQuiz'],
                                ], function () {
                                    Route::post('', [AfBulkImportController::class, 'importModuleQuizzes']);
                                });
                            });
                        });
                        Route::group([
                            'prefix' => '{courseModuleId}/lessons',
                        ], function () {
                            Route::post('', [AfLessonController::class, 'createLesson'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);
                            Route::put('sort', [AfLessonController::class, 'sortLesson'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);
                            Route::post('{lessonId}', [AfLessonController::class, 'updateLesson']);
                            Route::delete('{lessonIds}', [AfLessonController::class, 'deleteLesson'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);
                            Route::group([
                                'prefix' => '{lessonId}/ebook',
                            ], function () {
                                Route::get('', [AfLessonEbookController::class, 'getLessonEbook']);
                                Route::group([
                                    'middleware' => ['permission:' . PermissionData::EBOOK_MANAGEMENT],
                                ], function () {
                                    Route::post('', [AfLessonEbookController::class, 'createLessonEbook']);
                                    Route::post('{ebookId}', [AfLessonEbookController::class, 'updateLessonEbook']);
                                    Route::delete('{ebookId}', [AfLessonEbookController::class, 'deleteLessonEbook'])->middleware(['isCourseStatus:' . CourseStatusData::DRAFT . ',' . CourseStatusData::COMING_SOON]);
                                });
                            });
                            Route::group([
                                'prefix' => '{lessonId}/quizzes',
                            ], function () {
                                Route::get('', [AfQuizController::class, 'getLessonQuiz']);
                                Route::group([
                                    'prefix' => 'bulk',
                                ], function () {
                                    Route::get('', [AfBulkImportController::class, 'getLessonBulkImports']);
                                    Route::group([
                                        'middleware' => ['permission:' . PermissionData::BULK_UPLOAD_QUIZZES, 'AfCanUploadLessonQuiz'],
                                    ], function () {
                                        Route::post('', [AfBulkImportController::class, 'importLessonQuizzes']);
                                    });
                                });
                            });
                        });
                    });
                });
            });
        });
        // Routes for salary scale discounts
        Route::group([
            'prefix' => 'salary-scale-discounts',
            'middleware' => ['permission:' . PermissionData::SALARY_SCALE_DISCOUNTS_MANAGEMENT]
        ], function () {
            Route::put('', [AfCourseController::class, 'updateDiscountStatus']);
        });
    });
    Route::group([
        'prefix' => 'lesson-faqs',
    ], function () {
        Route::get('{lessonId}', [AfLessonFaqController::class, 'getLessonFaqList']);
        Route::post('', [AfLessonFaqController::class, 'createLessonFaq']);
        Route::put('{id}', [AfLessonFaqController::class, 'updateLessonFaq']);
        Route::delete('{id}', [AfLessonFaqController::class, 'deleteLessonFaq']);
    });
    Route::group([
        'prefix' => 'refunds',
    ], function () {
        Route::post('users/{id}', [AfPurchaseController::class, 'refund'])->middleware(['permission:' . PermissionData::REFUNDS_MANAGEMENT, 'IsUserDeleted']);
        Route::get('', [AfPurchaseController::class, 'getRefundedItems'])->middleware(['permission:' . PermissionData::VIEW_REFUNDS]);
    });
    Route::group([
        'prefix' => 'global-notifications',
        'middleware' => ['permission:' . PermissionData::GLOBAL_NOTIFICATIONS_MANAGEMENT],
    ], function () {
        Route::get('', [AfGlobalNotificationsController::class, 'getGlobalNotificationList']);
        Route::post('', [AfGlobalNotificationsController::class, 'createGlobalNotification']);
        Route::get('{id}', [AfGlobalNotificationsController::class, 'getGlobalNotification']);
        Route::put('{id}', [AfGlobalNotificationsController::class, 'updateGlobalNotification']);
        Route::delete('{id}', [AfGlobalNotificationsController::class, 'deleteGlobalNotification']);
    });
    Route::group([
        'prefix' => 'adverts',
        'middleware' => ['permission:' . PermissionData::ADVERT_MANAGEMENT],
    ], function () {
        Route::get('', [AfAdvertController::class, 'getAdvertList']);
        Route::post('', [AfAdvertController::class, 'createAdvert']);
        Route::get('{id}', [AfAdvertController::class, 'getAdvert']);
        Route::post('{id}', [AfAdvertController::class, 'updateAdvert']);
        Route::delete('{id}', [AfAdvertController::class, 'deleteAdvert']);
        Route::post('sort', [AfAdvertController::class, 'sortingAdvert']);
    });
    Route::group([
        'prefix' => 'events',
        'middleware' => ['permission:' . PermissionData::EVENT_MANAGEMENT],
    ], function () {
        Route::post('', [AfEventController::class, 'createEvent']);
        Route::get('', [AfEventController::class, 'getEventsList']);
        Route::get('{id}', [AfEventController::class, 'getEvent']);
        Route::post('{id}', [AfEventController::class, 'updateEvent']);
        Route::delete('{id}', [AfEventController::class, 'deleteEvent']);
    });
    Route::group([
        'prefix' => 'tiers',
        // 'middleware' => ['permission:' . PermissionData::IN_APP_TIERS]
    ], function () {
        Route::get('all', [AfInAppTiersController::class, 'getAllTiers']);
    });
    Route::group([
        'prefix' => 'products',
        'middleware' => ['permission:' . PermissionData::PHYSICAL_PRODUCT_MANAGEMENT]
    ], function () {
        Route::post('', [AfProductController::class, 'createProduct']);
        Route::get('', [AfProductController::class, 'getProductList']);
        Route::get('{id}', [AfProductController::class, 'getProduct']);
        Route::post('{id}', [AfProductController::class, 'updateProduct']);
        Route::delete('{id}', [AfProductController::class, 'deleteProduct']);
    });

    Route::group([
        'prefix' => 'coupons',
        'middleware' => ['permission:' . PermissionData::COUPON_MANAGEMENT],
    ], function () {
        Route::post('', [AfCouponController::class, 'createCoupon']);
        Route::get('', [AfCouponController::class, 'getCouponList']);
        Route::get('{id}', [AfCouponController::class, 'getCoupon']);
        Route::put('{id}', [AfCouponController::class, 'updateCoupon']);
    });
});

Route::group([
    'middleware' => ['auth:api', 'role:' . RoleData::MASTER_ADMIN],
    'prefix' => 'ma',
], function () {
    Route::group([
        'prefix' => 'ha',
    ], function () {
        Route::get('', [MaAdminManipulationController::class, 'getHaAdminList']);
        Route::delete('{id}', [MaAdminManipulationController::class, 'deleteHaAdmin']);
    });
});

// GU stands for guest
Route::group([
    'prefix' => 'gu',
], function () {
    Route::group([
        'prefix' => 'tickets',
    ], function () {
        Route::post('', [GuTicketController::class, 'submitTicket']);
        Route::get('subjects', [GuTicketController::class, 'getTicketSubjectList']);
        Route::get('subjects/{id}', [GuTicketController::class, 'getTicketSubject']);
    });
    Route::group([
        'prefix' => 'faqs',
    ], function () {
        Route::group([
            'prefix' => 'categories',
        ], function () {
            Route::get('', [GuFaqController::class, 'getRootFaqCategoryList']);
            Route::get('{id}', [GuFaqController::class, 'getSubFaqCategoryList']);
            Route::get('{id}/items', [GuFaqController::class, 'getFaqForCategory']);
        });
        Route::get('', [GuFaqController::class, 'searchFaq']);
        Route::get('{id}', [GuFaqController::class, 'getFaq']);
    });
    Route::group([
        'prefix' => 'courses',
    ], function () {
        Route::get('available', [GuCourseController::class, 'getGuCourseAvailableList']);
        //Get list of course_modules that contain ebooks
        Route::get('{courseId}/level/1/ebooks', [GuEbookController::class, 'getEbookListPerLevel']);

        Route::get('{id}', [GuCourseController::class, 'getCourse']);
        Route::get('{courseId}/level/{value}', [GuCourseController::class, 'getCourseLevel']);
        Route::get('coming-soon', [GuCourseController::class, 'getGuCourseComingSoonList']);
    });

    // Routes for products
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [GuProductController::class, 'fetch']);
        Route::get('{id}', [GuProductController::class, 'singleProduct']);
        Route::group(['prefix' => 'categories'], function () {
            Route::get('products-by-category', [GuProductController::class, 'fetchByCategory']);
            Route::get('{id}', [GuProductController::class, 'fetchCategory']);
        });
        Route::get('top-books', [GuProductController::class, 'topBooks']);
    });

    // Routes for Guest Cart
    Route::group(['prefix' => 'guest-carts'], function () {
        Route::post('create', [GuCartController::class, 'create']);
        Route::get('guest-cart/{cartId}', [GuCartController::class, 'fetch']);
        Route::delete('{cartId}', [GuCartController::class, 'delete']);
        Route::post('map-cart-items', [GuCartController::class, 'fetchCombinedCartProducts']);
    });
});

Route::group([
    'prefix' => 'gdpr',
], function () {
    Route::group([
        'prefix' => 'user',
    ], function () {
        Route::get('{uuid}', [IuGdprController::class, 'downloadGdprZip']);
    });
});

Route::group([
    'middleware' => ['auth:api', 'role:' . RoleData::ADMIN . '|' . RoleData::HEAD_ADMIN . '|' . RoleData::MASTER_ADMIN],
    'prefix' => 'admins',
], function () {
    Route::group([
        'prefix' => 'notifications',
    ], function () {
        Route::get('me', [AfNotificationController::class, 'getMyNotificationList']);
        Route::put('all/read', [AfNotificationController::class, 'markAllNotificationsRead']);
    });
});
