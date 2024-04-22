<?php

namespace Database\Seeders;

use App\DataObject\PermissionData;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::updateOrCreate(
            [
                'id' => PermissionData::FAQ_MANAGEMENT,
            ],
            [
                'name' => 'faqEntries_CRUD',
                'display_name' => 'FAQ Entries CRUD',
                'description' => 'Allows an administrator to create, read, update and delete FAQ entries under the FAQ module',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::TICKET_SUBJECT_MANAGEMENT,
            ],
            [
                'name' => 'ticketSubjects_CRUD',
                'display_name' => 'Ticket Subjects CRUD',
                'description' => "Allows an administrator to create, read, update and delete 'Ticket Subject' under the Contact Support module",
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::TICKET_CONTENT_MANAGEMENT,
            ],
            [
                'name' => 'ticketAgentForCategoryContent_RU',
                'display_name' => 'Ticket Agent for category Content RU',
                'description' => 'Allows an administrator to View and Respond to Tickets under the CONTENT category (Contact Support)',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::TICKET_SYSTEM_MANAGEMENT,
            ],
            [
                'name' => 'ticketAgentForCategorySystem_RU',
                'display_name' => 'Ticket Agent for category System RU',
                'description' => 'Allows an administrator to View and Respond to Tickets under the SYSTEM category (Contact Support)',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::FAQ_CATEGORY_MANAGEMENT,
            ],
            [
                'name' => 'faqCategories_CRUD',
                'display_name' => 'FAQ Categories CRUD',
                'description' => 'Allows an administrator to create, read, update and delete FAQ categories under the FAQ module',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::USER_MANAGEMENT,
            ],
            [
                'name' => 'userRecords_RU',
                'display_name' => 'User Records RU',
                'description' => "Allows an administrator to view user records, enable/disable user, view user's enrolled courses",
                'related_permissions' => 'userPurchaseHistory_R permission needed to view purchase history tab. '
                    . 'userRecords_D permission needed to delete the user',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::DELETE_USERS,
            ],
            [
                'name' => 'userRecords_D',
                'display_name' => 'User Records D',
                'description' => 'Allows an administrator to DELETE the user',
                'related_permissions' => 'userRecords_RU permission needed to perform this action',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::VIEW_USERS_PURCHASE_HISTORY,
            ],
            [
                'name' => 'userPurchaseHistories_R',
                'display_name' => 'User Purchase Histories R',
                'description' => "Allows an administrator to view user's purchase history entries",
                'related_permissions' => 'userRecords_RU permission needed to perform this action',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::TICKET_REFUND_MANAGEMENT,
            ],
            [
                'name' => 'ticketAgentForCategoryRefund_RU',
                'display_name' => 'Ticket Agent for category Refund RU',
                'description' => 'Allows an administrator to View and Respond to Tickets under the REFUND category (Contact Support)',
                'related_permissions' => 'refundPayment_C permission needed to perform the refund',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::REFUNDS_MANAGEMENT,
            ],
            [
                'name' => 'refundPayments_C',
                'display_name' => 'Refund Payments C',
                'description' => 'Allows an administrator to perform a REFUND action',
                'related_permissions' => 'userPurchaseHistory_R and userRecords_RU permission needed to perform this action',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::VIEW_REFUNDS,
            ],
            [
                'name' => 'refundPayments_R',
                'display_name' => 'Refund Payments C',
                'description' => 'Allows an administrator to view REFUND entries',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::TICKET_TEAM_LEADERSHIP,
            ],
            [
                'name' => 'ticketEscalation_R',
                'display_name' => 'Ticket Escalation R',
                'description' => 'Administrator will receive an email notification about unclaimed tickets',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::GLOBAL_NOTIFICATIONS_MANAGEMENT,
            ],
            [
                'name' => 'globalnotificationsEntries_CRUD',
                'display_name' => 'Global Notifications CRUD',
                'description' => "Allows an administrator to create, read, update and delete 'Global Notification' entries",
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::TICKET_GDPR_MANAGEMENT,
            ],
            [
                'name' => 'ticketAgentForCategoryGdpr_RU',
                'display_name' => 'Ticket Agent for category Gdpr RU',
                'description' => 'Allows an administrator to View and Respond to Tickets under the GDPR category (Contact Support)',
                'related_permissions' => 'gdpr_C and userRecords_RU permission needed to initiate processing of the GDPR request',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::GDPR_MANAGEMENT,
            ],
            [
                'name' => 'gdpr_C',
                'display_name' => 'GDPR C',
                'description' => 'Allows an administrator to create a GDPR request for a user, including all associated files and data',
                'related_permissions' => 'userRecords_RU permission needed to perform this action',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::ADVERT_MANAGEMENT,
            ],
            [
                'name' => 'advert_CRUD',
                'display_name' => 'Advert CRUD',
                'description' => 'Allows an administrator to create, read, update and delete adverts',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::CATEGORY_MANAGEMENT,
            ],
            [
                'name' => 'category_CRUD',
                'display_name' => 'Category CRUD',
                'description' => 'Allows an administrator to create, read, update and delete category',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::EVENT_MANAGEMENT,
            ],
            [
                'name' => 'event_CRUD',
                'display_name' => 'Event CRUD',
                'description' => 'Allows an administrator to create, read, update and delete events',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::COURSE_MANAGEMENT,
            ],
            [
                'name' => 'course_CRUD',
                'display_name' => 'Course CRUD',
                'description' => 'Allows an administrator to create, read, update and delete Courses',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::UPDATE_COURSE_STATUS,
            ],
            [
                'name' => 'courseStatus_U',
                'display_name' => 'Course Status U',
                'description' => 'Allows an administrator to update course status',
                'related_permissions' => 'course_CRUD permission needed to perform this action',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::BULK_UPLOAD_QUIZZES,
            ],
            [
                'name' => 'bulkUploadQuizzes_C',
                'display_name' => 'Bulk Upload Quizzes C',
                'description' => 'Allows an administrator to bulk upload quizzes/exams',
                'related_permissions' => 'course_CRUD permission needed to perform this action',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::EBOOK_MANAGEMENT,
            ],
            [
                'name' => 'eNotes_CRUD',
                'display_name' => 'Lesson E-Notes CRUD',
                'description' => 'Allows an administrator to create, read, update and delete lesson e-notes',
                'related_permissions' => 'course_CRUD permission needed to perform this action',
            ]
        );
        Permission::updateOrCreate(
            [
                'id' => PermissionData::TICKET_LESSON_QA_MANAGEMENT,
            ],
            [
                'name' => 'ticketAgentForCategoryLessonQ&A_RU',
                'display_name' => 'Ticket Agent for category Lesson Q&A RU',
                'description' => 'Allows an administrator to View and Respond to Tickets under the LESSON Q&A category',
                'related_permissions' => '',
            ]
        );

        // Admin Permission for Managing Products
        Permission::updateOrCreate(
            [
                'id' => PermissionData::PHYSICAL_PRODUCT_MANAGEMENT,
            ],
            [
                'name' => 'physicalProducts_CRUD',
                'display_name' => 'Physical Products CRUD',
                'description' => 'Allows an administrator to create, read, update and delete physical products for e-shop',
                'related_permissions' => '',
            ]
        );
        Permission::updateOrCreate(
            [
                'id'            => PermissionData::COUPON_MANAGEMENT,
            ],
            [
                'name'          => 'coupon_CRUD',
                'display_name'  => 'Coupon CRUD',
                'description'   => 'Allows an administrator to create, read, update and delete coupons',
                'related_permissions' => ''
            ]
        );

        // Permissions for managing salary scale discounts
        Permission::updateOrCreate(
            [
                'id'            => PermissionData::SALARY_SCALE_DISCOUNTS_MANAGEMENT,
            ],
            [
                'name'          => 'salary_scale_CRUD',
                'display_name'  => 'Salary Scale CRUD',
                'description'   => 'Allows an administrator to create, read, update and delete salary scale discounts',
                'related_permissions' => ''
            ]
        );
        Permission::updateOrCreate(
            [
                'id'            => PermissionData::COUPON_MANAGEMENT,
            ],
            [
                'name'          => 'coupon_CRUD',
                'display_name'  => 'Coupon CRUD',
                'description'   => 'Allows an administrator to create, read, update and delete coupons',
                'related_permissions' => ''
            ]
        );
    }
}
