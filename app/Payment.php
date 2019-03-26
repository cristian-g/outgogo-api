<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth0\Login\Facade\Auth0;

class Payment extends Model
{
    use Uuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quantity',
    ];

    protected $appends = [
        'am_i_owner',
    ];

    /**
     * Get the related vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Vehicle', 'vehicle_id');
    }

    /**
     * Get the related user.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Get the related receiver user.
     */
    public function receiver()
    {
        return $this->belongsTo('App\User', 'receiver_id');
    }

    /**
     * Get the original payment.
     */
    public function originalPayment()
    {
        return $this->belongsTo('App\Payment', 'original_payment');
    }

    /**
     * Get the related action.
     */
    public function action()
    {
        return $this->hasOne('App\Action');
    }

    public function getAmIOwnerAttribute()
    {
        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        return $this->user()->first()->id === $user->id;
    }
}
