<?php

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IuUserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public $email, $dateOfBirth, $password, $parentEmail;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param string $email
     * @param string $dateOfBirth
     * @param string $password
     * @param string|null $parentEmail
     * 
     * @return void
     */
    public function __construct(User $user, $email, $dateOfBirth, $password, $parentEmail)
    {
        $this->user = $user;
        $this->email = $email;
        $this->dateOfBirth = $dateOfBirth;
        $this->password = $password;
        $this->parentEmail = $parentEmail;
    }
}
