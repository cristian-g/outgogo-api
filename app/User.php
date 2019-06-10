<?php

namespace App;

use Auth0\Login\Contract\stdClass;
use Auth0\Login\Contract\the;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth0\Login\Contract\Auth0UserRepository;

class User extends Authenticatable implements Auth0UserRepository
{
    use Notifiable, Uuids;

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
        'name', 'surnames', 'email', 'password', 'auth0id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the vehicles of this user.
     */
    public function vehicles()
    {
        return $this->belongsToMany('App\Vehicle');
    }

    /**
     * This class is used on api authN to fetch the user based on the jwt.
     * @param stdClass $jwt with the data provided in the JWT
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getUserByDecodedJWT($jwt)
    {
        /*
         * The `sub` claim in the token represents the subject of the token
         * and it is always the `user_id`
         */
        $jwt->user_id = $jwt->sub;

        return $this->upsertUser($jwt);
    }

    /**
     * @param array $userInfo representing the user profile and user accessToken
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getUserByUserInfo($userInfo)
    {
        return $this->upsertUser($userInfo['profile']);
    }

    protected function upsertUser($profile) {

        // Note: Requires configured database access
        $user = User::where("auth0id", $profile->user_id)->first();

        if ($user === null) {

            $user_by_email = User::where("email", $profile->email)->first();

            if ($user_by_email === null) {
                // If not, create one
                $user = new User();
                $user->email = $profile->email; // you should ask for the email scope
                $user->auth0id = $profile->user_id;
                $user->name = $profile->name; // you should ask for the name scope
                $user->save();
            }
            else {
                $user_by_email->update([
                    "auth0id" => $profile->user_id,
                    //"name" => $profile->name,
                ]);
            }
        }

        return $user;
    }

    /**
     * @param $identifier the user id
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getUserByIdentifier($identifier)
    {
        //Get the user info of the user logged in (probably in session)
        $user = \App::make('auth0')->getUser();

        if ($user === null) return null;

        // build the user
        $user = $this->getUserByUserInfo($user);

        // it is not the same user as logged in, it is not valid
        if ($user && $user->auth0id == $identifier) {
            return $user;
        }
    }
}
