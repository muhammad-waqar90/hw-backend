<?php

namespace App\Transformers\AF;

use App\DataObject\CurrencyData;
use App\Models\Tier;
use League\Fractal\TransformerAbstract;

class AfInAppTiersAllTransformer extends TransformerAbstract
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
            'name' => CurrencyData::POUND.$tier->value.' ('.$tier->label.')',
        ];
    }
}
