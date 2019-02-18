<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutgoCategory extends Model
{
    use Uuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'outgo_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key_name',
    ];

    /**
     * Get the outgoes of this vehicle.
     */
    public function outgoes()
    {
        return $this->hasMany('App\Outgo', 'vehicle_id');
    }
}
