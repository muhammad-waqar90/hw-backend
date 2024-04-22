<?php

namespace App\Traits;

// https://laravel.com/docs/9.x/upgrade#str-function
// TODO: string laravel's helper global function
trait StringManipulationTrait
{
    public function truncate($string, $length = 255, $append = "..."): string
    {
        $string = trim($string);
        if (strlen($string) <= $length)
            return $string;

        $string = wordwrap($string, $length);

        $string = explode("\n", $string, 2);
        if (strlen($string[0]) > $length)
            return substr($string[0], 0, $length) . $append;

        return $string[0] . $append;
    }
}
