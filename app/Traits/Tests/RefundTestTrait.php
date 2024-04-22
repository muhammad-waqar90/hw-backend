<?php

namespace App\Traits\Tests;

use App\Models\PurchaseHistory;
use App\Models\Refund;
use App\Models\PurchaseItem;
use App\Models\Course;
use App\Models\Category;

trait RefundTestTrait
{
    public function RefundSeeder($user)
    {
        $purchaseHistory = PurchaseHistory::factory()->withUserId($user->id)->create();
        $category = Category::factory()->create();
        $course = Course::factory()->withId($category->id)->create();
        $purchaseItem = PurchaseItem::factory()->withCourseEntityId($course->id)->create();
        $refund = Refund::factory()->create();

        $data = new \stdClass();
        $data->refund = $refund;

        return $data;
    }
}