<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth0\Login\Facade\Auth0;

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

    protected $appends = [
        'sharing_status',
        'balance',
        'am_i_owner',
        'emails',
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

    public function getSharingStatusAttribute()
    {
        //return $this->users()->first()->email;
        //return "example@example.com";

        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        $relation_data = DB::table('user_vehicle')->where([
            "vehicle_id" => $this->id,
            "user_id" => $user->id,
        ])->first();
        //return \GuzzleHttp\json_encode($relation_data->is_owner);

        if ($relation_data->is_owner === 1) {
            $shares = DB::table('user_vehicle')->where([
                "vehicle_id" => $this->id,
                "is_owner" => false,
            ])->get();
            $names = [];
            foreach ($shares as $share) {
                $user_share = User::where('id', $share->user_id)->first();
                $names[] = $user_share->name;
            }
            if (count($names) === 0) {
                $message = "No compartido";
                return $message;
            }
            else {
                return "Compartido con " . $this->natural_language_join($names);
            }
        }
        else {
            $relation_data_owner = DB::table('user_vehicle')->where([
                "vehicle_id" => $this->id,
                "is_owner" => true,
            ])->first();
            $owner = User::find($relation_data_owner->user_id)->first();
            return $owner->name;
        }

        return null;
    }

    /**
     * Join a string with a natural language conjunction at the end.
     * https://gist.github.com/angry-dan/e01b8712d6538510dd9c
     */
    private function natural_language_join(array $list, $conjunction = 'y') {
        $last = array_pop($list);
        if ($list) {
            return implode(', ', $list) . ' ' . $conjunction . ' ' . $last;
        }
        return $last;
    }

    public function getBalanceAttribute()
    {
        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        $matchThese = ['vehicle_id' => $this->id, 'user_id' => $user->id];
        $balance =
            DB::table('payments')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese)->get()->first()->amount -
            DB::table('outgoes')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese)->get()->first()->amount;

        return $balance;
    }

    public function getAmIOwnerAttribute()
    {
        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        $relation_data = DB::table('user_vehicle')->where([
            "vehicle_id" => $this->id,
            "user_id" => $user->id,
        ])->first();

        return $relation_data->is_owner;
    }

    public function getEmailsAttribute()
    {
        // Compute existing users (already attached)
        $user_emails = [];
        $users = $this->users()->get();
        foreach ($users as $aux_user) {
            // Only add it if it is NOT the owner
            $relation_data = DB::table('user_vehicle')->where([
                "vehicle_id" => $this->id,
                "user_id" => $aux_user->id,
            ])->first();
            if ($relation_data->is_owner === 0) {
                $user_emails[] = $aux_user->email;
            }
        }

        return $user_emails;
    }
}
