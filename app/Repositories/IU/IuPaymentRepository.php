<?php

namespace App\Repositories\IU;

use App\Models\Customer;
use App\Models\InAppPayment;
use App\Models\StripePayment;
use App\Models\User;

class IuPaymentRepository
{

    private Customer $customer;
    private StripePayment $stripePayment;
    private InAppPayment $inAppPayment;

    public function __construct(Customer $customer, StripePayment $stripePayment, InAppPayment $inAppPayment)
    {
        $this->customer = $customer;
        $this->stripePayment = $stripePayment;
        $this->inAppPayment = $inAppPayment;
    }

    public function updateOrCreateCustomer(User $user)
    {
        return $this->customer->updateOrCreate(
            [
                'user_id' => $user->id
            ]
        );
    }

    public function saveStripePayment($payment)
    {
        return $this->stripePayment->create([
            'stripe_id'         => $payment ? $payment->id : null,
            'stripe_object'     => $payment ? $payment->object : null
        ]);
    }

    public function saveInAppPaymentReceipt($receipt)
    {
        return $this->inAppPayment->create([
            'transaction_id'        => $receipt['transactionId'],
            'transaction_receipt'   => $receipt['transactionReceipt']
        ]);
    }
}
