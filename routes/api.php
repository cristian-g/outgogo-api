<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// ------------------------------
// Vehicles
// ------------------------------
Route::get('/vehicles', 'VehicleController@index')->middleware('jwt');
Route::post('/vehicle', 'VehicleController@store')->middleware('jwt');//->middleware('check.scope:read:email');

// ------------------------------
// Outgoes
// ------------------------------
Route::get('/outgoes', 'OutgoController@index');
Route::post('/outgoes', 'OutgoController@store');
