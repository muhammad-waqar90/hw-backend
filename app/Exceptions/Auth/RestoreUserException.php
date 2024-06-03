<?php

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Support\Facades\Lang;

class RestoreUserException extends Exception
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
        parent::__construct();
    }

    public function getErrors(): array
    {
        return [
            'errors' => Lang::get('auth.accountTrashed'),
            'token' => $this->token,
        ];
    }
}
