<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use Uuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'brand',
        'model',
        'private_key',
        'public_key',
        'purchase_year',
        'purchase_price',
    ];

    /**
     * Get the outgoes of this vehicle.
     */
    public function outgoes()
    {
        return $this->hasMany('App\Outgo', 'vehicle_id');
    }

    /**
     * Get the users of this vehicle.
     */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
