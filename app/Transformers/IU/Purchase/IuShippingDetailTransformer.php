<?php

namespace App\Transformers\IU\Purchase;

use App\Models\ShippingDetail;
use League\Fractal\TransformerAbstract;

class IuShippingDetailTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(ShippingDetail $shippingDetail)
    {
        return [
            'id' => $shippingDetail->id,
            'address' => $shippingDetail->address,
            'city' => $shippingDetail->city,
            'country' => $shippingDetail->country,
            'postal_code' => $shippingDetail->postal_code,
            'shipping_cost' => $shippingDetail->shipping_cost,
        ];
    }
}
