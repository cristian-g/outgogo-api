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

        self::storeDistributions($original_outgo, $request, $vehicle, $user, $outgoCategory, (abs($request->quantity) * (-1)));

        return response()->json(null, 200);
    }

    public static function storeDistributions($original_outgo, $request, $vehicle, $user, $outgoCategory, $quantity) {
        // Distribute the outgo to current existing users
        $users = $vehicle->users()->get();
        $n_users = sizeof($users);
        foreach ($users as $aux_user) {
            $outgo = new Outgo([
                'quantity' => $quantity / $n_users,
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
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeConsumption(Request $request, $vehicle_id)
    {
        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        $vehicle = Vehicle::where([
            "id" => $vehicle_id,
        ])->first();

        $outgoCategory = OutgoCategory::where([
            'key_name' => 'drive'
        ])->first();

        $liters = $request->gas_liters;
        $gasPrice = $request->gas_price;

        $quantity = $liters * $gasPrice;
        $description = 'Consumo de ' . $liters . ' litros * ' . $gasPrice . ' €/litro = ' . $quantity . ' €';

        $outgo = new Outgo([
            'quantity' => $quantity,
            'description' => $description,
            'gas_liters' => $liters,
            'gas_price' => $gasPrice,
            'notes' => $request->notes,
            'share_outgo' => true,
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

        self::storeDistributions($outgo, $request, $vehicle, $user, $outgoCategory, $quantity);

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

        $quantity = $request->quantity;
        // Update distributions
        self::updateDistributions($outgo, (abs($quantity) * (-1)));

        return response()->json(null, 200);
    }

    public static function updateDistributions($outgo, $quantity) {
        // Update distributions
        $distributions = $outgo->distributions()->where('receiver_id', '!=' , DB::raw('user_id'))->with(['user', 'receiver'])->get();
        $n_distributions = sizeof($distributions) + 1;
        foreach ($distributions as $distribution) {
            $distribution->update([
                'quantity' => $quantity / $n_distributions,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateConsumption(Request $request, $id)
    {
        $outgo = Outgo::find($id);

        $liters = $request->gas_liters;
        $gasPrice = $request->gas_price;

        $quantity = $liters * $gasPrice;
        $description = 'Consumo de ' . $liters . ' litros * ' . $gasPrice . ' €/litro = ' . $quantity . ' €';

        $outgo->update([
            'quantity' => $quantity,
            'description' => $description,
            'gas_liters' => $liters,
            'gas_price' => $gasPrice,
            'notes' => $request->notes,
        ]);
        $outgo->save();

        // Update distributions
        self::updateDistributions($outgo, $quantity);

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
