<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait UtilsTrait
{
    /**
     * @param  mixed  $value
     */
    public function existInArray($value, array $stack): bool
    {
        return count(array_intersect((array) $value, $stack)) ? true : false;
    }

    /**
     * check isMinor
     *
     * @param  date  $dateOfBirth
     * @param  int  $from
     * @return bool
     */
    private function isMinor($dateOfBirth, $from = 13)
    {
        return $dateOfBirth > Carbon::now()->subYears($from)->format('Y-m-d');
    }

    /**
     * @param  int  $value
     * @param  string  $unit  [H]
     * @return \DateTimeInterface
     */
    public function addTimeToCurrentDate($value, $unit)
    {
        // TODO: required to generate a data-object file for holding units and expiry for all instances
        return $unit === 'H'
            ? now()->addHours($value)
            : now()->addSeconds($value);
    }

    /**
     * fun is used for getting the array depth based on a specific key
     */
    public function getAssArrayDepth(array $source, string $key, int $depth = 0): int
    {
        if (is_array($source[$key]) && count($source[$key]))
            $depth = $this->getAssArrayDepth($source[$key], $key, $depth + 1);

        return $depth;
    }
}
