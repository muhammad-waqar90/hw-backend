<?php

namespace App\Mail\IU\Certificate;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuCertificateEmail extends AbstractMail
{
    public $certificateId;
    public $entityType;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     */
    public function __construct($userProfile, $certificateId, $entityType)
    {
        parent::__construct($userProfile);
        $this->certificateId = $certificateId;
        $this->entityType = $entityType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(Lang::get('email.subjects.certificate'))->view('emails.IU.Certificate.iuCertificateEmail');
    }
}
