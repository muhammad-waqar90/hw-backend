<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkImportStatus extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function admin()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function adminProfile() {
        return $this->hasOne('App\Models\AdminProfile');
    }
}
