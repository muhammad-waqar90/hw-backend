<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateManipulationTrait {

    public function addDaysToDate($date, $days)
    {
        $parseDate = Carbon::createFromFormat('!Y-m-d', $date);
        return $parseDate->addDays($days);
    }
}
