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
Route::get('/vehicle/{id}', 'VehicleController@show')->middleware('jwt');
Route::put('/vehicle/{id}', 'VehicleController@update')->middleware('jwt');
Route::post('/vehicle', 'VehicleController@store')->middleware('jwt');//->middleware('check.scope:read:email');

// ------------------------------
// Actions
// ------------------------------
Route::get('/vehicle/{vehicle_id}/actions', 'ActionController@index')->middleware('jwt');

// ------------------------------
// Outgoes
// ------------------------------
Route::post('/vehicle/{vehicle_id}/outgo', 'OutgoController@store')->middleware('jwt');
Route::get('/outgo/{id}', 'OutgoController@show')->middleware('jwt');
Route::put('/outgo/{id}', 'OutgoController@update')->middleware('jwt');

// ------------------------------
// Payments
// ------------------------------
Route::post('/vehicle/{vehicle_id}/payment', 'PaymentController@store')->middleware('jwt');
Route::get('/payment/{id}', 'PaymentController@show')->middleware('jwt');
Route::put('/payment/{id}', 'PaymentController@update')->middleware('jwt');

// ------------------------------
// From vehicle
// ------------------------------
Route::post('drive', 'OutgoController@storeFromVehicle');
Route::post('list', 'OutgoController@indexFromVehicle');

// ------------------------------
// Fake
// ------------------------------
Route::get('/fake1', 'VehicleController@fake1');
Route::get('/fake2', 'VehicleController@fake2');
