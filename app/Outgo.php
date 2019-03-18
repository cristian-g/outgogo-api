<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outgo extends Model
{
    use Uuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'outgoes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description', 'quantity', 'notes', 'share_outgo', 'points', 'initial_liters', 'finished_at'
    ];

    protected $appends = ['category'];

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
     * Get the outgo category.
     */
    public function outgoCategory()
    {
        return $this->belongsTo('App\OutgoCategory', 'outgo_category_id');
    }

    public function getCategoryAttribute()
    {
        return $this->outgoCategory()->first()->getKeyName();
    }

    /**
     * Get the related action.
     */
    public function action()
    {
        return $this->hasOne('App\Action');
    }
}
