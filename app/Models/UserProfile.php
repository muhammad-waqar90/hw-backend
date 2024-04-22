<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that not are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'deleted_at', 'created_at', 'updated_at'
    ];

    protected $hidden = [
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
        'snapchat_url',
        'youtube_url',
        'pinterest_url'
    ];

    public function role()
    {
        return $this->belongsTo('App\Models\User');
    }
}
