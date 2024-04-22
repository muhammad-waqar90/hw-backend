<?php

namespace App\Traits\Tests;

use App\Models\PurchaseHistory;
use App\Models\PurchaseItem;

trait PurchaseItemTestTrait
{
    public function PaymentSeeder($user, $course)
    {
        $purchaseHistory = PurchaseHistory::factory()->withUserId($user->id)->create();
        PurchaseItem::factory()->withPurchaseHistoryId($purchaseHistory->id)->create();
    }
}
