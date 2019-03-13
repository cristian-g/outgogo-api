<?php

namespace App\Http\Controllers;

use App\User;
use App\Vehicle;
use Auth0\Login\Facade\Auth0;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();
        $vehicles = $user->vehicles()->orderBy('created_at', 'desc')->get();
        return response()->json(['vehicles'=> $vehicles->toArray()], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userInfo = Auth0::jwtUser();
        $owner = User::where('auth0id', $userInfo->sub)->first();
        $bytes = 40;
        $vehicle = new Vehicle([
            'brand' => $request->brand,
            'model' => $request->model,
            'private_key' => bin2hex(openssl_random_pseudo_bytes($bytes)),// will generate a random string of alphanumeric characters of length = $bytes * 2
            'public_key' => $request->key,//bin2hex(openssl_random_pseudo_bytes(40)),//'a39u',
            'purchase_year' => $request->year,
            'purchase_price' => $request->price,
        ]);
        $vehicle->save();

        // Attach owner
        $vehicle->users()->attach($owner, [
            'public_key' => '2f4c',
            'is_owner' => true
        ]);

        // Attach other users
        foreach ($request->emails as $email) {
            $user = User::where('email', $email)->first();
            $vehicle->users()->attach($user, [
                'public_key' => bin2hex(openssl_random_pseudo_bytes(40)),
                'is_owner' => false
            ]);
        }

        return response()->json(null, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        $vehicle = Vehicle::find($id);

        //$vehicle["actions"] = $vehicle->actions()->orderBy('created_at', 'desc')->get()->toArray();


        $vehicle["actions"] = $vehicle
            ->actions()
            ->with(['outgo', 'payment'])
            ->orderBy('created_at', 'desc')
            //->groupBy(DB::raw("DAY(created_at)"))
            ->get()
            ->toArray();

        $matchThese = ['vehicle_id' => $vehicle->id, 'user_id' => $user->id];
        $vehicle["balance"] =
            DB::table('payments')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese)->get()->first()->amount -
            DB::table('outgoes')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese)->get()->first()->amount;



        return response()->json(['vehicle'=> $vehicle], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);

        $bytes = 40;
        $vehicle->update([
            'brand' => $request->brand,
            'model' => $request->model,
            'private_key' => bin2hex(openssl_random_pseudo_bytes($bytes)),// will generate a random string of alphanumeric characters of length = $bytes * 2
            'public_key' => $request->key,//bin2hex(openssl_random_pseudo_bytes(40)),//'a39u',
            'purchase_year' => $request->year,
            'purchase_price' => $request->price,
        ]);
        $vehicle->save();

        // Attach other users
        foreach ($request->emails as $email) {
            $user = User::where('email', $email)->first();
            $vehicle->users()->attach($user, [
                'public_key' => bin2hex(openssl_random_pseudo_bytes(40)),
                'is_owner' => false
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
