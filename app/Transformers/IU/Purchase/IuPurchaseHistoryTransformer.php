<?php

namespace App\Transformers\IU\Purchase;

use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\Models\PurchaseHistory;
use League\Fractal\TransformerAbstract;

class IuPurchaseHistoryTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'shippingDetails',
    ];

    /**
     * List of resources possible to include
     */
    protected array $availableIncludes = [
        'purchaseItems',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(PurchaseHistory $purchaseHistory)
    {
        return [
            'id' => $purchaseHistory->id,
            'amount' => $purchaseHistory->amount,
            'entity_type' => PurchaseHistoryEntityData::ENTITY_TYPE[$purchaseHistory->entity_type]['type'],
            'currency_symbol' => PurchaseHistoryEntityData::ENTITY_TYPE[$purchaseHistory->entity_type]['currency_symbol'],
            'created_at' => $purchaseHistory->created_at,
            'updated_at' => $purchaseHistory->updated_at,
        ];
    }

    public function includePurchaseItems(PurchaseHistory $purchaseHistory)
    {
        return $this->collection($purchaseHistory->purchaseItems, new IuPurchaseItemTransformer());
    }

    public function includeShippingDetails(PurchaseHistory $purchaseHistory)
    {
        return $this->collection($purchaseHistory->shippingDetails, new IuShippingDetailTransformer());
    }
}
