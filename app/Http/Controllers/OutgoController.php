<?php

namespace App\Http\Controllers;

use App\Action;
use App\Outgo;
use App\OutgoCategory;
use App\User;
use Illuminate\Http\Request;
use App\Vehicle;
use Illuminate\Support\Facades\DB;
use Auth0\Login\Facade\Auth0;

class OutgoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeFromVehicle(Request $request)
    {
        $vehicle = Vehicle::where([
            "private_key" => $request->vehicle_private_key,
        ])->first();

        $relation_data = DB::table('user_vehicle')->where([
            "vehicle_id" => $vehicle->id,
            "public_key" => $request->user_public_key,
        ])->first();

        $user = User::find($relation_data->user_id);

        $outgo = new Outgo([
            'quantity' => $request->quantity,
            'description' => $request->description,
            //'notes' => $request->notes, // only add them if filled in request!
            //'share_outgo' => $request->share_outgo, // only add them if filled in request!
            //'points' => $request->points, // only add them if filled in request!
        ]);

        $outgoCategory = OutgoCategory::where([
            'key_name' => 'drive'
        ])->first();

        $outgo->vehicle()->associate($vehicle);
        $outgo->user()->associate($user);
        $outgo->outgoCategory()->associate($outgoCategory);

        $outgo->save();

        return response()->json(null, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $vehicle_id)
    {
        $vehicle = Vehicle::where([
            "id" => $vehicle_id,
        ])->first();

        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        $action = new Action([
        ]);

        $outgo = new Outgo([
            'quantity' => $request->quantity,
            'description' => $request->description,
            'notes' => $request->notes,
            'share_outgo' => $request->share_outgo,
            'points' => $request->quantity * 100,
        ]);

        $outgoCategory = OutgoCategory::where([
            'key_name' => 'drive'
        ])->first();

        $outgo->vehicle()->associate($vehicle);
        $outgo->user()->associate($user);
        $outgo->outgoCategory()->associate($outgoCategory);

        $outgo->save();

        $action->outgo_id = $outgo->id;
        $action->vehicle()->associate($vehicle);
        $action->save();

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
        //
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
        //
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
