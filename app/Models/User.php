<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that not are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'deleted_at', 'pivot',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function verifyUser()
    {
        return $this->hasOne(VerifyUser::class);
    }

    public function verifyUserAge()
    {
        return $this->hasOne(VerifyUserAge::class);
    }

    public function permGroups()
    {
        return $this->belongsToMany(PermGroup::class);
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class)->withPivot('id', 'created_at', 'updated_at');
    }

    public function identityVerification()
    {
        return $this->hasOne(IdentityVerification::class);
    }

    public function restoreUser()
    {
        return $this->hasOne(RestoreUser::class);
    }

    public function shippingDetails()
    {
        return $this->hasMany(ShippingDetail::class);
    }

    public function salaryScale()
    {
        return $this->hasOne(UserSalaryScale::class);
    }
}
