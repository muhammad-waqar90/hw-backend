<?php

namespace App\Mail\AF\Order;

use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\Models\PurchaseHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class AfNewOrderConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $purchaseHistoryId;
    public $purchaseHistory;
    public $userProfile;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $purchaseHistoryId
     */

    public function __construct($userProfile, $purchaseHistoryId)
    {
        $this->purchaseHistoryId = $purchaseHistoryId;
        $this->userProfile = $userProfile;

    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        $this->purchaseHistory = PurchaseHistory::where('user_id', $this->userProfile->userProfile->user_id)
            ->where('id', $this->purchaseHistoryId)
            ->with('purchaseItems', 'shippingDetails')
            ->first();

        $this->purchaseHistory->currency_symbol = PurchaseHistoryEntityData::ENTITY_TYPE[$this->purchaseHistory->entity_type]['currency_symbol'];

        return $this->subject(Lang::get('email.subjects.newOrderConfirmation'))
            ->view('emails.AF.Order.afNewOrderConfirmationEmail');
    }
}
