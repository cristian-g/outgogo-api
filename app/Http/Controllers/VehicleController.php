<?php

namespace App\Http\Controllers;

use App\Action;
use App\Outgo;
use App\OutgoCategory;
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
        return response()->json(['vehicles' => $vehicles->toArray()], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
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
            $user_share = User::where('email', $email)->first();
            $vehicle->users()->attach($user_share, [
                'public_key' => bin2hex(openssl_random_pseudo_bytes(40)),
                'is_owner' => false
            ]);
        }

        return response()->json(null, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vehicle = Vehicle::find($id);

        $vehicle["actions"] = $vehicle
            ->actions()
            ->with(['outgo', 'outgo.user', 'payment', 'payment.user', 'payment.receiver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        // Identified user
        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        // User ids to make a payment
        $users = $vehicle->users()->get();
        $user_ids = [];
        foreach ($users as $aux_user) {
            if ($aux_user->id === $user->id) continue;
            $user_ids[] = $aux_user;
        }
        $vehicle["user_ids"] = $user_ids;

        // Balances
        $balances = [];
        foreach ($users as $aux_user) {
            if ($aux_user->id === $user->id) continue;
            $matchThese = ['vehicle_id' => $vehicle->id, 'user_id' => $user->id, 'receiver_id' => $aux_user->id];
            $matchThese2 = ['vehicle_id' => $vehicle->id, 'user_id' => $aux_user->id, 'receiver_id' => $user->id];
            $balance =
                DB::table('payments')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese)->get()->first()->amount -
                DB::table('outgoes')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese)->get()->first()->amount +
                DB::table('outgoes')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese2)->get()->first()->amount;
            $aux_user["balance"] = $balance;
            $balances[] = $aux_user;
        }
        $vehicle["balances"] = $balances;

        return response()->json(['vehicle' => $vehicle], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
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
     * Fake 1
     *
     * @return \Illuminate\Http\Response
     */
    public function fake1()
    {
        $user = User::where('email', 'angela.brunet@gmail.com')->first();
        $vehicle = $user->vehicles()->first();

        $gasPrice = 1.26;

        $liters = 2.4;
        $quantity = $liters * $gasPrice;
        $description = 'Consumo de ' . $liters . ' litros * ' . $gasPrice . ' €/litro = ' . $quantity . ' €';

        $outgo = new Outgo([
            'quantity' => $quantity,
            'description' => $description,
            'initial_liters' => 20.2,
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

        $original_outgo = $outgo;

        $action = new Action([
        ]);

        $action->outgo_id = $outgo->id;
        $action->vehicle()->associate($vehicle);
        $action->save();

        // Distribute the outgo to current existing users
        $users = $vehicle->users()->get();
        $n_users = sizeof($users);
        foreach ($users as $aux_user) {
            $outgo = new Outgo([
                'quantity' => (abs($quantity)) / $n_users,
                'description' => ($description == null) ? "" : $description,
                'notes' => "",
                'share_outgo' => true,
                'points' => abs($quantity) * 100,
            ]);
            $outgo->vehicle()->associate($vehicle);
            $outgo->user()->associate($user);
            $outgo->receiver()->associate($aux_user);
            $outgo->originalOutgo()->associate($original_outgo);
            $outgo->outgoCategory()->associate($outgoCategory);
            $outgo->save();
        }

        return response()->json(['success' => true], 200);
    }

    /**
     * Fake 2
     *
     * @return \Illuminate\Http\Response
     */
    public function fake2()
    {
        $user = User::where('email', 'pol.vales@gmail.com')->first();
        $vehicle = $user->vehicles()->first();

        $gasPrice = 1.26;

        $liters = 50;
        $quantity = $liters * $gasPrice;
        $description = 'Ha puesto ' . $liters . ' litros * ' . $gasPrice . ' €/litro = ' . $quantity . ' €';

        $outgo = new Outgo([
            'quantity' => $quantity * (-1),
            'description' => $description,
            'initial_liters' => 0,
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

        $original_outgo = $outgo;

        $action = new Action([
        ]);

        $action->outgo_id = $outgo->id;
        $action->vehicle()->associate($vehicle);
        $action->save();

        // Distribute the outgo to current existing users
        $users = $vehicle->users()->get();
        $n_users = sizeof($users);
        foreach ($users as $aux_user) {
            $outgo = new Outgo([
                'quantity' => ($quantity * (-1)) / $n_users,
                'description' => ($description == null) ? "" : $description,
                'notes' => "",
                'share_outgo' => true,
                'points' => abs($quantity) * 100,
            ]);
            $outgo->vehicle()->associate($vehicle);
            $outgo->user()->associate($user);
            $outgo->receiver()->associate($aux_user);
            $outgo->originalOutgo()->associate($original_outgo);
            $outgo->outgoCategory()->associate($outgoCategory);
            $outgo->save();
        }

        return response()->json(['success' => true], 200);
    }
}
