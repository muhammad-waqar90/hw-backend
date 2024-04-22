<?php

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Support\Facades\Lang;

class PendingEmailVerificationException extends Exception
{
    public function getErrors(): array
    {
        return [
            'errors'        => Lang::get('auth.emailVerificationPending'),
            'unverified'    => true
        ];
    }
}
