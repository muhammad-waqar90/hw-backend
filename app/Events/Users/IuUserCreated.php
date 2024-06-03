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

    public string $email;

    public string $dateOfBirth;

    public string $password;

    public ?string $parentEmail;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $email, string $dateOfBirth, string $password, ?string $parentEmail)
    {
        $this->user = $user;
        $this->email = $email;
        $this->dateOfBirth = $dateOfBirth;
        $this->password = $password;
        $this->parentEmail = $parentEmail;
    }
}
