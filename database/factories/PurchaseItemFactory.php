<?php

namespace Database\Factories;

use App\DataObject\Purchases\PurchaseItemStatusData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $purchase_history_id = DB::table('purchase_histories')->pluck('id');
        $entity_id = DB::table('courses')->pluck('id');

        return [
            'purchase_history_id' => $purchase_history_id->random(),
            'amount' => fake()->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 800),
            'course_id' => $entity_id->random(),
            'entity_type' => PurchaseItemTypeData::COURSE,
            'entity_id' => $entity_id->random(),
            'entity_name' => Str::random(10),
            'status' => PurchaseItemStatusData::PAID,
        ];
    }

    public function withPurchaseHistoryId($id)
    {
        return $this->state(fn () => [
            'purchase_history_id' => $id,
        ]);
    }

    public function withCourseEntityId($id)
    {
        return $this->state(fn () => [
            'entity_id' => $id,
            'entity_type' => PurchaseItemTypeData::COURSE,
        ]);
    }

    public function withEbookEntityId($id)
    {
        return $this->state(fn () => [
            'entity_id' => $id,
            'entity_type' => PurchaseItemTypeData::EBOOK,
        ]);
    }

    public function withExamEntityId($id)
    {
        return $this->state(fn () => [
            'entity_id' => $id,
            'entity_type' => PurchaseItemTypeData::EXAM,
        ]);
    }

    public function paid()
    {
        return $this->state(fn () => [
            'status' => PurchaseItemStatusData::PAID,
        ]);
    }

    public function refunded()
    {
        return $this->state(fn () => [
            'status' => PurchaseItemStatusData::REFUNDED,
        ]);
    }

    public function freeItem()
    {
        return $this->state(fn () => [
            'amount' => 0,
        ]);
    }
}
