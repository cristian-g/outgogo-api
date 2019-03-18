<?php

namespace App\Http\Controllers;

use App\Action;
use App\Outgo;
use App\OutgoCategory;
use App\User;
use Carbon\Carbon;
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

        $outgoCategory = OutgoCategory::where([
            'key_name' => 'drive'
        ])->first();

        $lastOutgo = Outgo::where([
            "vehicle_id" => $vehicle->id,
            "user_id" => $relation_data->user_id,
        ])->orderBy('updated_at', 'desc')->first();
        if ($lastOutgo != null) {
            $now = Carbon::now();
            $diffInSeconds = $now->diffInSeconds($lastOutgo->finished_at);
        }

        $gasPrice = 1.26;

        if ($lastOutgo == null || $diffInSeconds > 45) {

            $liters = 0;
            $quantity = $liters * $gasPrice;
            $description = 'Consumo de ' . $liters . ' litros * ' . $gasPrice . ' €/litro = ' . $quantity . ' €';

            $outgo = new Outgo([
                'quantity' => $quantity,
                'description' => $description,
                'initial_liters' => $request->liters,
                //'notes' => $request->notes, // only add them if filled in request!
                //'share_outgo' => $request->share_outgo, // only add them if filled in request!
                //'points' => $request->points, // only add them if filled in request!
            ]);

            $outgo->vehicle()->associate($vehicle);
            $outgo->user()->associate($user);
            $outgo->outgoCategory()->associate($outgoCategory);

            $outgo->save();

            $action = new Action([
            ]);
            $action->outgo_id = $outgo->id;
            $action->vehicle()->associate($vehicle);
            $action->save();
        }
        else {
            $liters = $lastOutgo->initial_liters - $request->liters;
            $quantity = $liters * $gasPrice;
            $description = 'Consumo de ' . $liters . ' litros * ' . $gasPrice . ' €/litro = ' . $quantity . ' €';

            $lastOutgo->update([
                'quantity' => $quantity,
                'description' => $description,
            ]);
            $lastOutgo->finished_at = $now;
            $lastOutgo->save();
        }

        return response()->json(null, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function indexFromVehicle(Request $request)
    {
        $vehicle = Vehicle::where([
            "private_key" => $request->vehicle_private_key,
        ])->first();

        $actions = $vehicle
            ->actions()
            ->with(['outgo', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return response()->json(['actions'=> $actions], 200);
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
        $outgo = Outgo::find($id);
        return response()->json(['outgo'=> $outgo], 200);
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
        $outgo = Outgo::find($id);

        $outgo->update([
            'quantity' => $request->quantity,
            'description' => $request->description,
            'notes' => $request->notes,
            'share_outgo' => $request->share_outgo,
            'points' => $request->quantity * 100,
        ]);
        $outgo->save();

        return response()->json(null, 200);
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
