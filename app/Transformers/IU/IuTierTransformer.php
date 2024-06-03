<?php

namespace App\Transformers\IU;

use App\Models\Tier;
use League\Fractal\TransformerAbstract;

class IuTierTransformer extends TransformerAbstract
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
        ];
    }
}
