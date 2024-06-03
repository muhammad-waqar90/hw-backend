<?php

namespace App\Transformers\IU\Purchase;

use App\Models\PurchaseItem;
use League\Fractal\TransformerAbstract;

class IuPurchaseItemTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(PurchaseItem $purchaseItem)
    {
        return [
            'id' => $purchaseItem->id,
            'amount' => $purchaseItem->amount,
            'summary' => $purchaseItem->summary,
            'name' => $purchaseItem->entity_name,
            'type' => $purchaseItem->entity_type,
            'status' => $purchaseItem->status,
            'created_at' => $purchaseItem->created_at,
            'updated_at' => $purchaseItem->updated_at,
        ];
    }
}
