<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalNotification extends Model
{
    use HasFactory;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function globalNotificationUser()
    {
        return $this->belongsToMany('App\Models\User')->withTimestamps();
    }

    public function adminProfile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AdminProfile::class, 'user_id', 'user_id');
    }
}