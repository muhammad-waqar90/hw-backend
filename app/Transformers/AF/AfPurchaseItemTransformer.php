<?php

namespace App\Transformers\AF;

use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\DataObject\QuizData;
use App\Models\PurchaseItem;
use League\Fractal\TransformerAbstract;

class AfPurchaseItemTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param PurchaseItem $purchaseItem
     * @return array
     */
    public function transform(PurchaseItem $purchaseItem)
    {
        return [
            'id'                    => $purchaseItem->id,
            'purchase_history_id'   => $purchaseItem->purchase_history_id,
            'amount'                => $purchaseItem->amount,
            'summary'               => $purchaseItem->summary,
            'name'                  => $purchaseItem->entity_name,
            'type'                  => $purchaseItem->entity_type,
            'status'                => $purchaseItem->status,
            'created_at'            => $purchaseItem->created_at,
            'updated_at'            => $purchaseItem->updated_at,
            'attempts_left'         => $purchaseItem->attempts_left,
            'exam_status'           => $purchaseItem->exam_type ? ($purchaseItem->score > QuizData::DEFAULT_PASSING_SCORE ? 'Pass' : 'Fail') : null,
            'exam_total_attempts'   => QuizData::QUIZ_EXAM_ALLOWED_ATTEMPTS,
            'entity_type'           => PurchaseHistoryEntityData::ENTITY_TYPE[$purchaseItem->purchaseHistory->entity_type]['type'],
            'currency_symbol'       => PurchaseHistoryEntityData::ENTITY_TYPE[$purchaseItem->purchaseHistory->entity_type]['currency_symbol']
        ];
    }
}
