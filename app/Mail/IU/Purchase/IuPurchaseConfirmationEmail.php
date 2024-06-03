<?php

namespace App\Mail\IU\Purchase;

use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Mail\AbstractMail;
use App\Models\PurchaseHistory;
use Illuminate\Support\Facades\Lang;

class IuPurchaseConfirmationEmail extends AbstractMail
{
    public $purchaseHistoryId;

    public $purchaseHistory;

    /**
     * Create a new message instance.
     */
    public function __construct($userProfile, $purchaseHistoryId)
    {
        parent::__construct($userProfile);
        $this->purchaseHistoryId = $purchaseHistoryId;
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
            ->with('purchaseItems')
            ->withCount(['purchaseItems' => function ($q) {
                $q->where('entity_type', '!=', PurchaseItemTypeData::SHIPPING);
            }])
            ->first();
        $this->purchaseHistory->currency_symbol = PurchaseHistoryEntityData::ENTITY_TYPE[$this->purchaseHistory->entity_type]['currency_symbol'];

        return $this->subject(Lang::get('email.subjects.purchaseConfirmation'))
            ->view('emails.IU.Purchase.purchaseConfirmation');
    }
}
