<?php

namespace App\Mail\IU\GDPR;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuGdprExportDataEmail extends AbstractMail
{
    public $uuid;

    /**
     * Create a new message instance.
     */
    public function __construct($userProfile, $uuid)
    {
        parent::__construct($userProfile);
        $this->uuid = $uuid;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(Lang::get('email.subjects.gdprExportData'))->view('emails.IU.GDPR.iuGdprExportDataEmail');
    }
}
