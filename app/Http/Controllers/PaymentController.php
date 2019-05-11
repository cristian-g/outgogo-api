<?php

namespace App\Http\Controllers;

use App\Action;
use App\Payment;
use App\User;
use App\Vehicle;
use Illuminate\Http\Request;
use Auth0\Login\Facade\Auth0;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                'de usuario destino del pago' => $request->receiver,
            ),
            array(
                'cantidad' => array('required', 'numeric'),
                'de usuario destino del pago' => array('required'),
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

        $payment = new Payment([
            'quantity' => $request->quantity,
        ]);

        $payment->vehicle()->associate($vehicle);
        $payment->user()->associate($user);

        $receiver = User::find($request->receiver);
        $payment->receiver()->associate($receiver);

        $payment->save();

        $action->payment_id = $payment->id;
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
        $payment = Payment::find($id);
        $payment->user = $payment->user()->first();
        $payment->receiver = $payment->receiver()->first();
        return response()->json(['payment'=> $payment], 200);
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
        $payment = Payment::find($id);

        $payment->update([
            'quantity' => $request->quantity,
        ]);
        $payment->save();

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
