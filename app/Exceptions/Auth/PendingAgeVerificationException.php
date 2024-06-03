<?php

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Support\Facades\Lang;

class PendingAgeVerificationException extends Exception
{
    protected $username;

    public function __construct($username)
    {
        $this->username = $username;
        parent::__construct();
    }

    public function getErrors(): array
    {
        return [
            'errors' => Lang::get('auth.parentEmailVerificationPending'),
            'unverified_parent' => true,
            'username' => $this->username,
        ];
    }
}
