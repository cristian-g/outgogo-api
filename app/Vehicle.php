<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Get the actions of this vehicle.
     */
    public function actions()
    {
        return $this->hasMany('App\Action', 'vehicle_id');
    }

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
