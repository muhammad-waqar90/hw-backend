<?php

namespace App\Transformers\IU\Payment;

use App\Models\Customer;
use League\Fractal\TransformerAbstract;

class IuCustomerTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Customer $customer)
    {
        return [
            'id' => $customer->id,
            'card_brand' => $customer->pm_type,
            'card_last_four' => $customer->pm_last_four,
            'trial_ends_at' => $customer->trial_ends_at,
            'created_at' => $customer->created_at,
            'updated_at' => $customer->updated_at,
        ];
    }
}
