<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'items'
    ];

    protected $casts = [
        'items' => AsArrayObject::class
    ];
}
