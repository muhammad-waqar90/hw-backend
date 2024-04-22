<?php

namespace App\Repositories\AF;

use App\Models\Tier;

class AfInAppTiersRepository
{

    private Tier $tier;

    public function __construct(Tier $tier)
    {
        $this->tier = $tier;
    }

    public function getAllTiers()
    {
        return $this->tier
            ->select('id', 'label', 'value')
            ->get();
    }
}
