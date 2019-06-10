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
use Illuminate\Support\Facades\Validator;

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

        $gasPrice = 1.25;

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

        // Validation
        $validation = Validator::make(
            array(
                'cantidad' => $request->quantity,
                'descripción' => $request->description,
            ),
            array(
                'cantidad' => array('required', 'numeric'),
                'descripción' => array('required'),
            )
        );
        if ($validation->fails() ) {
            $array = (array_values((array) $validation->messages()->toArray()));
            $array2 = [];
            foreach ($array as $element) {
                foreach ($element as $element2) {
                    array_push($array2, $element2);
                }
            }
            return response()->json(['errors' => $array2], 500);
        }

        $action = new Action([
        ]);

        $outgo = new Outgo([
            'quantity' => (abs($request->quantity) * (-1)),
            'description' => ($request->description == null) ? "" : $request->description,
            'notes' => ($request->notes == null) ? "" : $request->notes,
            'share_outgo' => $request->share_outgo,
            'points' => abs($request->quantity) * 100,
        ]);

        $outgoCategory = OutgoCategory::where([
            'key_name' => 'drive'
        ])->first();

        $outgo->vehicle()->associate($vehicle);
        $outgo->user()->associate($user);
        $outgo->outgoCategory()->associate($outgoCategory);

        $outgo->save();

        $original_outgo = $outgo;

        $action->outgo_id = $outgo->id;
        $action->vehicle()->associate($vehicle);
        $action->save();

        // Distribute the outgo to current existing users
        $users = $vehicle->users()->get();
        $n_users = sizeof($users);
        foreach ($users as $aux_user) {
            $outgo = new Outgo([
                'quantity' => (abs($request->quantity) * (-1)) / $n_users,
                'description' => ($request->description == null) ? "" : $request->description,
                'notes' => ($request->notes == null) ? "" : $request->notes,
                'share_outgo' => $request->share_outgo,
                'points' => abs($request->quantity) * 100,
            ]);
            $outgo->vehicle()->associate($vehicle);
            $outgo->user()->associate($user);
            $outgo->receiver()->associate($aux_user);
            $outgo->originalOutgo()->associate($original_outgo);
            $outgo->outgoCategory()->associate($outgoCategory);
            $outgo->save();
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

        $outgo = Outgo::find($id);
        $distributions = $outgo->distributions()->where('receiver_id', '!=' , DB::raw('user_id'))->with(['user', 'receiver'])->get();
        $outgo->distributions = $distributions;
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
            'quantity' => (abs($request->quantity) * (-1)),
            'description' => ($request->description == null) ? "" : $request->description,
            'notes' => ($request->notes == null) ? "" : $request->notes,
            'share_outgo' => $request->share_outgo,
            'points' => abs($request->quantity) * 100,
        ]);
        $outgo->save();

        // Update distributions
        $distributions = $outgo->distributions()->where('receiver_id', '!=' , DB::raw('user_id'))->with(['user', 'receiver'])->get();
        $n_distributions = sizeof($distributions) + 1;
        foreach ($distributions as $distribution) {
            $distribution->update([
                'quantity' => (abs($request->quantity) * (-1)) / $n_distributions,
            ]);
        }

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
