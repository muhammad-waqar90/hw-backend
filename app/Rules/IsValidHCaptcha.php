<?php

namespace App\Rules;

use App\Services\HCaptcha\HCaptchaService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Lang;

class IsValidHCaptcha implements Rule
{
    private $token;

    /**
     * Create a new rule instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return HCaptchaService::verify($this->token);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('auth.invalidCaptcha');
    }
}
