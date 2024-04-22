<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userProfile;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     */
    public function __construct($userProfile)
    {
        $this->queue = 'high';
        $this->userProfile = $userProfile;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    abstract function build();
}
