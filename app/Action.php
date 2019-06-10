<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use Uuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_at',
    ];

    /**
     * Get the related vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Vehicle', 'vehicle_id');
    }

    //protected $appends = ['outgo', 'payment'];

    /**
     * Get the related outgo.
     */
    public function outgo()
    {
        return $this->belongsTo('App\Outgo', 'outgo_id');
    }

    /**
     * Get the related payment.
     */
    public function payment()
    {
        return $this->belongsTo('App\Payment', 'payment_id');
    }

    /*public function getOutgoAttribute()
    {
        return $this->outgoCategory()->first()->getKeyName();
    }*/
}
