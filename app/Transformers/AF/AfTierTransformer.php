<?php

namespace App\Transformers\AF;

use App\DataObject\CurrencyData;
use App\Models\Tier;
use League\Fractal\TransformerAbstract;

class AfTierTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Tier $tier)
    {
        return [
            'id' => $tier->id,
            'tier' => $tier->label,
            'value' => $tier->value,
            'label' => CurrencyData::POUND.$tier->value.' ('.$tier->label.')',
        ];
    }
}
