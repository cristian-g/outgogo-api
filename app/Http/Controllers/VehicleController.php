<?php

namespace App\Http\Controllers;

use App\Action;
use App\Outgo;
use App\OutgoCategory;
use App\User;
use App\Payment;
use App\Vehicle;
use Auth0\Login\Facade\Auth0;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        // Validation
        $validation = Validator::make(
            array(
                'marca' => $request->brand,
                'modelo' => $request->model,
            ),
            array(
                'marca' => array('required'),
                'modelo' => array('required'),
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

        $vehicle = new Vehicle([
            'brand' => $request->brand,
            'model' => $request->model,
        ]);
        $vehicle->save();

        // Attach owner
        $vehicle->users()->attach($owner, [
            'is_owner' => true
        ]);

        // Attach other users
        foreach ($request->emails as $email) {
            if ($email == '') continue;
            $user_share = User::where('email', $email)->first();
            if ($user_share == null) {
                $vehicle->users()->detach();
                $vehicle->delete();
                return response()->json(['errors' => ['El email introducido no pertenece a un usuario existente.']], 400);
            }
            $vehicle->users()->attach($user_share, [
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
                DB::table('outgoes')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese2)->get()->first()->amount -
                DB::table('payments')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese2)->get()->first()->amount;
            $aux_user["balance"] = $balance;
            $balances[] = $aux_user;
        }
        $vehicle["balances"] = $balances;

        return response()->json(['vehicle' => $vehicle], 200);
    }

    public function show_balance($id, $user_id)
    {
        $vehicle = Vehicle::find($id);

        // Identified user
        $userInfo = Auth0::jwtUser();
        $user = User::where('auth0id', $userInfo->sub)->first();

        // Desired user
        $aux_user = User::find($user_id);

        // Rules
        $matchThese = ['vehicle_id' => $vehicle->id, 'user_id' => $user->id, 'receiver_id' => $aux_user->id];
        $matchThese2 = ['vehicle_id' => $vehicle->id, 'user_id' => $aux_user->id, 'receiver_id' => $user->id];

        // Array to return
        $actions = [];

        // Balances
        $payments1 = Payment::where($matchThese)->get()->toArray();
        $payments2 = Payment::where($matchThese2)->get()->toArray();
        $outgoes1 = Outgo::where($matchThese)->get()->toArray();
        $outgoes2 = Outgo::where($matchThese2)->get()->toArray();

        // Label as poitive or negative
        foreach ($payments1 as $key => $payment) $payments1[$key]["positive"] = true;
        foreach ($payments2 as $key => $payment) $payments2[$key]["positive"] = false;
        foreach ($outgoes1 as $key => $outgo) {
            $outgoes1[$key]["positive"] = $outgoes1[$key]["quantity"] < 0;
        }
        foreach ($outgoes2 as $key => $outgo) {
            $outgoes2[$key]["positive"] = $outgoes2[$key]["quantity"] >= 0;
        }

        // Push to array
        foreach ($payments1 as $key => $payment) array_push($actions, $payment);
        foreach ($payments2 as $key => $payment) array_push($actions, $payment);
        foreach ($outgoes1 as $key => $outgo) array_push($actions, $outgo);
        foreach ($outgoes2 as $key => $outgo) array_push($actions, $outgo);

        // Sort array
        $array = $actions;
        foreach ($array as $key => $node) {
            $timestamps[$key]    = $node["created_at"];
        }
        array_multisort($timestamps, SORT_DESC, $array);
        $actions = $array;

        // Compute total
        $total =
            DB::table('payments')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese)->get()->first()->amount -
            DB::table('outgoes')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese)->get()->first()->amount +
            DB::table('outgoes')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese2)->get()->first()->amount -
            DB::table('payments')->select(DB::raw('SUM(quantity) AS amount'))->where($matchThese2)->get()->first()->amount;

        return response()->json([
            'actions' => $actions,
            'total' => $total,
            'user' => $aux_user
        ], 200);
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
        ]);
        $vehicle->save();

        // Compute existing users (already attached)
        $user_ids = [];
        $users = $vehicle->users()->get();
        foreach ($users as $aux_user) {
            $user_ids[] = $aux_user->id;
        }

        // Attach other users
        foreach ($request->emails as $email) {
            if ($email == '') continue;

            $user = User::where('email', $email)->first();

            // Only attach it if it is not already attached
            if (!in_array($user->id, $user_ids)) {
                $vehicle->users()->attach($user, [
                    'is_owner' => false
                ]);
            }
        }
    }
}
