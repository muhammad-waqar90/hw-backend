<?php

namespace App\Mail\IU\Refund;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuPurchasesRefundedEmail extends AbstractMail
{
    public $items;

    /**
     * Create a new message instance.
     */
    public function __construct($userProfile, $items)
    {
        parent::__construct($userProfile);
        $this->items = $items;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(Lang::get('email.subjects.purchaseRefunded'))
            ->view('emails.IU.Refund.purchaseRefunded');

    }
}
