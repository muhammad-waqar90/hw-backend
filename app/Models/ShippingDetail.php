<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_history_id',
        'user_id',
        'address',
        'city',
        'country',
        'postal_code',
        'shipping_partner',
        'shipping_cost',
    ];

    public function purchaseHistory()
    {
        return $this->belongsTo(PurchaseHistory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
