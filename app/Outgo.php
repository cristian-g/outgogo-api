<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outgo extends Model
{
    protected $table = 'outgoes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description', 'quantity',
    ];

    /**
     * Get the related car.
     */
    public function car()
    {
        return $this->belongsTo('App\Car', 'car_id');
    }
}
