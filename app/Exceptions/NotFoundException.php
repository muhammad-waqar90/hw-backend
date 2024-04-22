<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Lang;

class NotFoundException extends Exception
{

    public function report()
    {
        //
    }
    public function render($request)
    {
        return response()->json(['errors' => Lang::get('general.notFound')], 404);
    }
}
