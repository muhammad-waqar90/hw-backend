<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyUser extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function userProfile()
    {
        return $this->belongsTo('App\Models\UserProfile', 'user_id', 'user_id');
    }
}
