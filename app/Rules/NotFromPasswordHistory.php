<?php

namespace App\Rules;

use App\Repositories\PasswordHistoryRepository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Lang;

class NotFromPasswordHistory implements Rule
{
    private $user, $password;

    /**
     * Create a new rule instance.
     *
     * @param  User  $user
     * @param  string  $password
     * @return void
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !($this->user && PasswordHistoryRepository::isFromPasswordHistory($this->user->id, $this->password));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('auth.passwordFromHistory');
    }
}
