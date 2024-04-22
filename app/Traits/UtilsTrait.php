<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait UtilsTrait
{
    /**
     * @param mixed $value
     * @param array $stack
     * @return bool
     */
    public function existInArray($value, array $stack): bool
    {
        return count(array_intersect((array)$value, $stack)) ? true : false;
    }

    /**
     * check isMinor
     *
     * @param date $dateOfBirth
     * @param int $from
     *
     * @return bool
     */
    private function isMinor($dateOfBirth, $from = 13)
    {
        return $dateOfBirth > Carbon::now()->subYears($from)->format('Y-m-d');
    }

    /**
     * @param int $value
     * @param string $unit [H]
     *
     * @return \DateTimeInterface
     */
    public function addTimeToCurrentDate($value, $unit)
    {
        // TODO: required to generate a data-object file for holding units and expiry for all instances
        return $unit === 'H'
            ? now()->addHours($value)
            : now()->addSeconds($value);
    }
}
