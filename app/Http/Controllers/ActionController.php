<?php

namespace App\Http\Controllers;

use App\Outgo;
use App\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($vehicle_id)
    {
        //$vehicle = Vehicle::find($vehicle_id);
        //$outgoes = $vehicle->outgoes()->orderBy('created_at', 'asc')->get();
        //return response()->json(['actions'=> $outgoes->toArray()], 200);

        /*$vehicle = Vehicle::find($vehicle_id);
        $outgoes = $vehicle->outgoes()->orderBy('created_at', 'asc')->get();
        return response()->json(['actions'=> $outgoes->toArray()], 200);


        DB::table("clicks")
            ->select("id" ,DB::raw("(COUNT(*)) as total_click"))

            ->orderBy('created_at')

            ->groupBy(DB::raw("MONTH(created_at)"))

            ->get();*/
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
