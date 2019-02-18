<?php

namespace App\Http\Controllers;

use App\Outgo;
use App\OutgoCategory;
use App\User;
use Illuminate\Http\Request;
use App\Vehicle;
use Illuminate\Support\Facades\DB;

class OutgoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $firstVehicle = Vehicle::first();
        $outgoes = Outgo::orderBy('created_at', 'asc')->get();
        return response()->json(['outgoes'=> $outgoes->toArray()], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
