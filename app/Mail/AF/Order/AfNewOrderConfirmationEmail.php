<?php

namespace App\Mail\AF\Order;

use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\DataObject\Purchases\PurchaseItemTypeData;
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

    public function __construct($userProfile, $purchaseHistoryId)
    {
        $this->userProfile = $userProfile;
        $this->purchaseHistoryId = $purchaseHistoryId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->purchaseHistory = PurchaseHistory::where('id', $this->purchaseHistoryId)
            ->with('purchaseItems', 'shippingDetails')
            ->withCount(['purchaseItems' => function ($q) {
                $q->where('entity_type', '!=', PurchaseItemTypeData::SHIPPING);
            }])
            ->first();

        $this->purchaseHistory->currency_symbol = PurchaseHistoryEntityData::ENTITY_TYPE[$this->purchaseHistory->entity_type]['currency_symbol'];

        return $this->subject(Lang::get('email.subjects.newOrderConfirmation'))
            ->view('emails.AF.Order.afNewOrderConfirmationEmail');
    }
}
