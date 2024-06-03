<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait DateManipulationTrait
{
    public function addDaysToDate($date, $days)
    {
        $parseDate = Carbon::createFromFormat('!Y-m-d', $date);

        return $parseDate->addDays($days);
    }
}
