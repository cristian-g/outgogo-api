<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the outgoes of this car.
     */
    public function outgoes()
    {
        return $this->hasMany('App\Outgo', 'car_id');
    }
}
